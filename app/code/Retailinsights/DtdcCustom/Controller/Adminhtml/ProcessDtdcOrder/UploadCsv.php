<?php
namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessDtdcOrder;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

class UploadCsv extends Action
{
    protected $resultRedirectFactory;
    protected $orderRepository;
    protected $invoiceResourceCountCollection;
    protected $csv;
    protected $resultJsonFactory;
    protected $messageManager;
    protected $resultFactory;

    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory $invoiceResourceCountCollection,
        \Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders\Grid\CollectionFactory $collectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        Action\Context $context
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->invoiceResourceCountCollection = $invoiceResourceCountCollection;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->csv = $csv;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            // Redirect URL for DTDC listing
            $returnUrl = $this->getUrl('dtdccustom/processdtdcorder/index');

            // Validate file upload
            if (!isset($_FILES['csv']['tmp_name']) || empty($_FILES['csv']['tmp_name'])) {
                $this->messageManager->addErrorMessage(__('No CSV file uploaded.'));
                return $this->resultRedirectFactory->create()->setPath($returnUrl);
            }

            $allowedTypes = ['text/csv', 'application/vnd.ms-excel'];
            if (!in_array($_FILES['csv']['type'], $allowedTypes)) {
                $this->messageManager->addErrorMessage(__('Invalid file format. Please upload a CSV file.'));
                return $this->resultRedirectFactory->create()->setPath($returnUrl);
            }

            $handle = fopen($_FILES['csv']['tmp_name'], "r");
            $headers = fgetcsv($handle, 1000, ",");
            $count = 0;
            $output = [];

            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $count++;
                $incrementId = trim($row[0]);

                if (empty($incrementId)) {
                    $output[] = "<span>Line $count: Order Increment ID cannot be empty</span><br/>";
                    continue;
                }

                $order = $this->loadOrderByIncrementId($incrementId);
                if (!$order || !$order->getId()) {
                    $output[] = "<span>{$incrementId}: Order not found or invalid</span><br/>";
                    continue;
                }

                $status = $order->getStatus();

                if (in_array($status, ['order_delivered', 'dispatched_to_courier'])) {
                    $output[] = "<span>{$incrementId}: Already {$status}</span><br/>";
                    continue;
                }

                $shipment = $order->getShipmentsCollection();
                $hasPayslip = $this->checkPayslipGenerated($order);
                $hasDtdcLabel = $this->checkDtdcLabel($order);

                if (empty($shipment->getData())) {
                    $output[] = "<span>{$incrementId}: Shipment not generated yet</span><br/>";
                } elseif ($hasPayslip === 'no') {
                    $output[] = "<span>{$incrementId}: Packing slip not downloaded</span><br/>";
                } elseif ($hasDtdcLabel === 'no') {
                    $output[] = "<span>{$incrementId}: DTDC label missing</span><br/>";
                } else {
                    $output[] = "<li style='list-style:none'><span class='valid_ids'>{$incrementId}</span></li>";
                }
            }

            fclose($handle);

            // Save result to data persistor (for displaying in UI)
            $persistor = $objectManager->get('Magento\Framework\App\Request\DataPersistorInterface');
            $persistor->set('dtdc_upload_result', $output);

            return $this->resultRedirectFactory->create()->setPath($returnUrl);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: ' . $e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('dtdccustom/processdtdcorder/index');
        }
    }

    protected function loadOrderByIncrementId($incrementId)
    {
        return $this->_objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId($incrementId);
    }

    protected function checkPayslipGenerated($order)
    {
        $invoiceId = '';
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceId = $invoice->getId();
        }

        $collection = $this->invoiceResourceCountCollection->create()
            ->addFieldToFilter('invoice_id', $invoiceId);

        foreach ($collection as $value) {
            if (trim($value['invoice_id']) == trim($invoiceId)) {
                return 'yes';
            }
        }

        return 'no';
    }

    protected function checkDtdcLabel($order)
    {
        $orderId = $order->getId();
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('tracking_title', ['eq' => 'dtdc']);

        if ($collection->getSize() > 0) {
            return 'yes';
        }
        return 'no';
    }
}
