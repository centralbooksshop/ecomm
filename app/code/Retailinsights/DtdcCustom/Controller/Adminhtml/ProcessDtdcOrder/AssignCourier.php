<?php

namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessDtdcOrder;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory;

class AssignCourier extends \Magento\Backend\App\Action
{
    protected $orderCollectionFactory;
    protected $processCBOOrdersFactory;
    protected $resultJsonFactory;
    protected $_resultPageFactory;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        ProcessCBOOrdersFactory $processCBOOrdersFactory,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->processCBOOrdersFactory = $processCBOOrdersFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $incrementIds = (array) $this->getRequest()->getPost('orderIds');
        $incrementIds = array_map('trim', $incrementIds);

        $result = [];
        foreach ($incrementIds as $incrementId) {
            $result[$incrementId] = $this->saveCourier($incrementId);
        }

        return $this->resultJsonFactory->create()->setData($result);
    }

    public function saveCourier($incrementId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($incrementId);

        if (!$order || !$order->getId()) {
            return 'failure';
        }

        $orderId = $order->getId();

        // Using Magento sales order collection (no DTDC table)
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect(['entity_id', 'increment_id', 'status', 'customer_email', 'shipsy_reference_numbers'])
            ->addFieldToFilter('entity_id', $orderId);

        $trackingNo = $collection->getFirstItem()->getData('shipsy_reference_numbers') ?? ''; // or assign manually if needed

        // Save to ProcessCBOOrders
        $model = $this->processCBOOrdersFactory->create();
        $model->addData([
            'order_id'        => $orderId,
            'driver_id'       => '',
            'tracking_title'  => 'DTDC',
            'tracking_number' => $trackingNo,
        ]);

        try {
            $model->save();

            // Update order status
            $order->setStatus('dispatched_to_courier')
                ->addStatusToHistory('dispatched_to_courier', __('Order dispatched to courier (DTDC).'))
                ->save();

            return 'success';
        } catch (\Exception $e) {
            return 'failure: ' . $e->getMessage();
        }
    }
}
