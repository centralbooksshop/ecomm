<?php
namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessOrder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders\Grid\CollectionFactory as DtdcCollectionFactory;
use Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory as InvoiceResourceCollectionFactory;
use Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory as FedexCollectionFactory;
use Psr\Log\LoggerInterface;

class CheckShipment extends Action
{
    protected $fedexLabels;
    protected $dtdcCollection;
    protected $invoiceResource;
    protected $orderRepository;
    protected $shipmentRepository;
    protected $resultJsonFactory;
    protected $orderCollectionFactory;
    protected $logger;

    public function __construct(
        FedexCollectionFactory $fedexLabels,
        DtdcCollectionFactory $dtdcCollection,
        InvoiceResourceCollectionFactory $invoiceResource,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderCollectionFactory $orderCollectionFactory,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);

        $this->fedexLabels            = $fedexLabels;
        $this->dtdcCollection         = $dtdcCollection;
        $this->invoiceResource        = $invoiceResource;
        $this->orderRepository        = $orderRepository;
        $this->shipmentRepository     = $shipmentRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->resultJsonFactory      = $resultJsonFactory;
        $this->logger                 = $logger;
    }

    /**
     * MAIN ENTRY – handles AJAX request
     */
    public function execute()
    {
        $orderIds = (array) $this->getRequest()->getParam('orderIds');
        $courier  = strtolower($this->getRequest()->getParam('courier'));

        $orderIds = array_unique(array_map('trim', $orderIds));
        $response = [];

        $total   = count($orderIds);
        $success = 0;
        $failed  = 0;

        foreach ($orderIds as $incrementId) {
            $result = $this->checkCourierStatus($incrementId, $courier);
            $response[$incrementId] = $result;

            if ($result['status'] === 'success') {
                $success++;
            } else {
                $failed++;
            }
        }

        $response['_summary'] = [
            'total'   => $total,
            'success' => $success,
            'failed'  => $failed
        ];

        $this->logger->info("Manual CheckShipment Summary | Courier: {$courier} | Total: {$total} | Assignable: {$success} | Failed: {$failed}");

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * DYNAMIC COURIER LOGIC HANDLER
     */
    private function checkCourierStatus($incrementId, $courier)
    {
        $order = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('increment_id', $incrementId)
            ->getFirstItem();

        if (!$order || !$order->getId()) {
            $this->logger->warning("CheckShipment Failed: {$incrementId} - Order not found");
            return [
                'status'  => 'failed',
                'message' => 'Order not found'
            ];
        }

        $status = $order->getStatus();

        if (in_array($status, ['dispatched_to_courier', 'order_delivered'])) {
            $this->logger->warning("CheckShipment Failed: {$incrementId} - Already {$status}");
            return [
                'status'  => 'failed',
                'message' => "Already {$status}"
            ];
        }

        if (!in_array($status, ['assigned_to_picker', 'processing', 'pending', 'complete'])) {
            $this->logger->warning("CheckShipment Failed: {$incrementId} - Invalid status {$status}");
            return [
                'status'  => 'failed',
                'message' => 'Courier selection may be pending / invalid order status'
            ];
        }

        if ($order->getShipmentsCollection()->count() === 0) {
            $this->logger->warning("CheckShipment Failed: {$incrementId} - Shipment not created");
            return [
                'status'  => 'failed',
                'message' => 'Shipment not created'
            ];
        }

        if ($this->isPayslipMissing($order)) {
            $this->logger->warning("CheckShipment Failed: {$incrementId} - Packing slip not downloaded");
            return [
                'status'  => 'failed',
                'message' => 'Packing slip not downloaded'
            ];
        }

        $hasTracking = $this->checkCourierTracking($order, $courier);

        if (!$hasTracking) {
            $this->logger->warning("CheckShipment Failed: {$incrementId} - Tracking missing or reassigned");
            return [
                'status'  => 'failed',
                'message' => 'Tracking missing / reassigned to another courier'
            ];
        }

        $this->logger->info("CheckShipment Success: {$incrementId} ready for courier {$courier}");

        return [
            'status'  => 'success',
            'message' => 'Ready to assign'
        ];
    }

    /**
     * Payslip check
     */
    private function isPayslipMissing($order)
    {
        $invoiceId = null;
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceId = $invoice->getId();
        }

        if (!$invoiceId) {
            return true;
        }

        $collection = $this->invoiceResource->create()
            ->addFieldToFilter('invoice_id', $invoiceId);

        return $collection->count() === 0;
    }

    /**
     * Courier-specific tracking handlers
     */
    private function checkCourierTracking($order, $courier)
    {
        $cboCourier = strtolower($order->getData('cbo_courier_name'));
        $cboRef     = $order->getData('cbo_reference_number');

        switch ($courier) {
            case 'dtdc':
                return ($cboCourier === 'dtdc' && !empty($cboRef));

            case 'smcs':
                return ($cboCourier === 'smcs' && !empty($cboRef));

            case 'delhivery':
                return ($cboCourier === 'delhivery' && !empty($cboRef));

            case 'elasticrun':
                return ($cboCourier === 'elasticrun' && !empty($cboRef));

            case 'amazon':
                return ($cboCourier === 'amazon' && !empty($cboRef));

            default:
                return false;
        }
    }

    /**
     * DTDC label check
     */
    private function checkDtdcLabel($order)
    {
        $orderRow = $this->orderCollectionFactory->create()
            ->addFieldToSelect(['shipsy_reference_numbers', 'cbo_courier_name', 'cbo_reference_number'])
            ->addFieldToFilter('entity_id', $order->getId())
            ->getFirstItem();

        if (!empty($orderRow->getData('shipsy_reference_numbers'))) {
            return true;
        }

        $courier = strtolower($orderRow->getData('cbo_courier_name'));
        $refNo   = $orderRow->getData('cbo_reference_number');

        if ($courier === 'dtdc' && !empty($refNo)) {
            return true;
        }

        return false;
    }

    /**
     * FedEx label check
     */
    private function checkFedexLabel($order)
    {
        $fedex = $this->fedexLabels->create()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('lstatus', 0)
            ->getFirstItem();

        return !empty($fedex->getData('order_id'));
    }
}