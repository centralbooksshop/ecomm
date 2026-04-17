<?php

namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessOrder;

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
        parent::__construct($context);

        $this->orderCollectionFactory     = $orderCollectionFactory;
        $this->processCBOOrdersFactory    = $processCBOOrdersFactory;
        $this->resultJsonFactory          = $resultJsonFactory;
        $this->_resultPageFactory         = $resultPageFactory;
    }

    public function execute()
    {
        $incrementIds = (array) $this->getRequest()->getPost('orderIds');
        $courier      = strtolower($this->getRequest()->getPost('courier'));   // NEW – Dynamic

        $response = [];
        foreach ($incrementIds as $incrementId) {
            $response[$incrementId] = $this->assignToCourier(trim($incrementId), $courier);
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * Save courier assignment + update order status
     */
    private function assignToCourier($incrementId, $courier)
    {
        // Load order using DI factory
        $order = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('increment_id', $incrementId)
            ->getFirstItem();

        if (!$order || !$order->getId()) {
            return ['status' => 'failure', 'msg' => 'Order not found'];
        }

        $orderId = $order->getId();

        // Fetch tracking number dynamically based on courier
        $trackingNumber = $this->getTrackingNumber($order, $courier);

        // Save to ProcessCBOOrders table
        try {
            $model = $this->processCBOOrdersFactory->create();
            $model->addData([
                'order_id'        => $orderId,
                'driver_id'       => '',
                'tracking_title'  => strtoupper($courier),
                'tracking_number' => $trackingNumber,
            ]);
            $model->save();
        } catch (\Exception $e) {
            return ['status' => 'failure', 'msg' => $e->getMessage()];
        }

        // Update order status to dispatched_to_courier
        try {
            $order->setStatus('dispatched_to_courier');
            $order->addStatusHistoryComment(
                __('Order dispatched to courier: %1', strtoupper($courier))
            );
            $order->save();
        } catch (\Exception $e) {
            return ['status' => 'failure', 'msg' => $e->getMessage()];
        }

        return ['status' => 'success'];
    }

	private function getTrackingNumber($order, $courier)
	{
		$data = $order->getData();
		$cboCourier = strtolower($data['cbo_courier_name'] ?? '');
		$cboRef     = $data['cbo_reference_number'] ?? '';

		switch ($courier) {

			case 'dtdc':

				if ($cboCourier === 'dtdc' && !empty($cboRef)) {
					return $cboRef;
				}

				return '';

			case 'delhivery':
				if ($cboCourier === 'delhivery' && !empty($cboRef)) {
					return $cboRef;
				}
				return '';

			case 'elasticrun':
				if ($cboCourier === 'elasticrun' && !empty($cboRef)) {
					return $cboRef;
				}
				return '';

			case 'amazon':
				if ($cboCourier === 'amazon' && !empty($cboRef)) {
					return $cboRef;
				}
				return '';
			case 'smcs':
			if ($cboCourier === 'smcs' && !empty($cboRef)) {
				return $cboRef;
			}
			return '';

			default:
				return '';
		}
	}

}
