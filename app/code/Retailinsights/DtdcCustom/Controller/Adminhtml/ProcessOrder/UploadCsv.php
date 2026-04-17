<?php
namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessOrder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory;
use Psr\Log\LoggerInterface;

class UploadCsv extends Action
{
    protected $resultRedirectFactory;
    protected $invoiceResourceCountCollection;
    protected $collectionFactory;
    protected $csv;
    protected $processCBOOrdersFactory;
    protected $logger;

    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory $invoiceResourceCountCollection,
        \Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders\Grid\CollectionFactory $collectionFactory,
        \Magento\Framework\File\Csv $csv,
        ProcessCBOOrdersFactory $processCBOOrdersFactory,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->invoiceResourceCountCollection = $invoiceResourceCountCollection;
        $this->collectionFactory = $collectionFactory;
        $this->csv = $csv;
        $this->processCBOOrdersFactory = $processCBOOrdersFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $objectManager = ObjectManager::getInstance();
            $persistor = $objectManager->get(\Magento\Framework\App\Request\DataPersistorInterface::class);
            $returnUrl = $this->getUrl('dtdccustom/processorder/index');

            if (empty($_FILES['csv']['tmp_name'])) {
                $this->messageManager->addErrorMessage(__('No CSV file uploaded.'));
                return $this->resultRedirectFactory->create()->setPath($returnUrl);
            }

            $rows = $this->csv->getData($_FILES['csv']['tmp_name']);
            unset($rows[0]); // remove header row

            $output = [];
            $successCount = 0;
            $failedCount = 0;
            $totalOrders = count($rows);

            foreach ($rows as $index => $row) {

                $lineNo = $index + 2; // actual CSV line number
                $incrementId = trim($row[0] ?? '');
                $courier = strtolower(trim($row[1] ?? ''));

                if ($incrementId === '') {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>Line {$lineNo}: Empty Order Increment ID</span><br/>";
                    $this->logger->error("CSV Upload Failed: Line {$lineNo} - Empty Order Increment ID");
                    continue;
                }

                if ($courier === '') {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: Courier name missing</span><br/>";
                    $this->logger->error("CSV Upload Failed: {$incrementId} - Courier name missing");
                    continue;
                }

                /** Load Order */
                $order = $this->loadOrderByIncrementId($incrementId);

                if (!$order || !$order->getId()) {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: Order not found</span><br/>";
                    $this->logger->error("CSV Upload Failed: {$incrementId} - Order not found");
                    continue;
                }

                $status = $order->getStatus();

                /** Already processed */
                if (in_array($status, ['order_delivered', 'dispatched_to_courier'])) {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: Already {$status}</span><br/>";
                    $this->logger->warning("CSV Upload Skipped: {$incrementId} - Already {$status}");
                    continue;
                }

                /** Shipment check */
                if (!$order->hasShipments()) {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: Shipment not created</span><br/>";
                    $this->logger->error("CSV Upload Failed: {$incrementId} - Shipment not created");
                    continue;
                }

                /** Payslip check */
                if ($this->checkPayslipGenerated($order) === 'no') {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: Packing slip not downloaded</span><br/>";
                    $this->logger->error("CSV Upload Failed: {$incrementId} - Packing slip not downloaded");
                    continue;
                }

                /** Tracking check */
                $tracking = $this->getTrackingNumber($order, $courier);

                if (empty($tracking)) {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: {$courier} tracking number missing / reassigned to another courier</span><br/>";
                    $this->logger->error("CSV Upload Failed: {$incrementId} - {$courier} tracking missing or courier mismatch");
                    continue;
                }

                /** Order ID */
                $orderId = $order->getId();

                /** Save in ProcessCBOOrders */
                try {
                    $model = $this->processCBOOrdersFactory->create();
                    $model->addData([
                        'order_id'        => $orderId,
                        'driver_id'       => '',
                        'tracking_title'  => strtoupper($courier),
                        'tracking_number' => $tracking,
                    ]);
                    $model->save();
                } catch (\Exception $e) {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: Failed to save CBO Order - {$e->getMessage()}</span><br/>";
                    $this->logger->critical("CSV Upload Failed: {$incrementId} - CBO save failed - " . $e->getMessage());
                    continue;
                }

                /** Update order status */
                try {
                    $order->setStatus('dispatched_to_courier');
                    $order->addStatusHistoryComment("Order assigned to {$courier} via CSV.");
                    $order->save();

                    $successCount++;
                    $output[] = "<span style='color:green;font-weight:bold;'>{$incrementId}: {$courier} assigned ({$tracking})</span><br/>";
                    $this->logger->info("CSV Upload Success: {$incrementId} assigned to {$courier} with tracking {$tracking}");
                } catch (\Exception $e) {
                    $failedCount++;
                    $output[] = "<span style='color:red;font-weight:bold;'>{$incrementId}: Failed while updating order status - {$e->getMessage()}</span><br/>";
                    $this->logger->critical("CSV Upload Failed: {$incrementId} - Status update failed - " . $e->getMessage());
                    continue;
                }
            }

            /** Summary block */
            $summary = "<div style='font-weight:bold; padding:10px; margin-bottom:10px; background:#f8f8f8; border:1px solid #ccc;'>
                            Total Orders: {$totalOrders} |
                            Assign Success: <span style='color:green;'>{$successCount}</span> |
                            Assign Failed: <span style='color:red;'>{$failedCount}</span>
                        </div>";

            array_unshift($output, $summary);

            $persistor->set('multi_courier_upload', $output);

            $this->logger->info("CSV Upload Summary: Total={$totalOrders}, Success={$successCount}, Failed={$failedCount}");

            return $this->resultRedirectFactory->create()->setPath($returnUrl);

        } catch (\Exception $e) {
            $this->logger->critical("CSV Upload Fatal Error: " . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Error: ' . $e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('dtdccustom/processorder/index');
        }
    }

    protected function loadOrderByIncrementId($incrementId)
    {
        return ObjectManager::getInstance()
            ->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId($incrementId);
    }

    /** Check Payslip */
    protected function checkPayslipGenerated($order)
    {
        $invoiceId = '';

        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceId = $invoice->getId();
        }

        if (!$invoiceId) {
            return 'no';
        }

        $collection = $this->invoiceResourceCountCollection
            ->create()
            ->addFieldToFilter('invoice_id', $invoiceId);

        return ($collection->getSize() > 0) ? 'yes' : 'no';
    }

    /** Get Tracking number from order */
    private function getTrackingNumber($order, $courier)
    {
        $data = $order->getData();
        $cboCourier = strtolower($data['cbo_courier_name'] ?? '');
        $cboRef = $data['cbo_reference_number'] ?? '';

        if ($cboCourier === $courier && !empty($cboRef)) {
            return $cboRef;
        }

        return '';
    }
}