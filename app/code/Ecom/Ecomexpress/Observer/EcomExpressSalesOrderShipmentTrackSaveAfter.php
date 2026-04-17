<?php


namespace Ecom\Ecomexpress\Observer;

use Magento\Framework\Event\ObserverInterface;

class EcomExpressSalesOrderShipmentTrackSaveAfter implements ObserverInterface {

	protected $_objectManager;
	
	public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager) {
		$this->_objectManager = $objectManager;
	}
	
	public function execute(\Magento\Framework\Event\Observer $observer) {	
		$track = $observer->getEvent()->getTrack();
		$order = $track->getShipment()->getOrder();
		$shippingMethod = $order->getShippingMethod();	
		if (!$shippingMethod || !$track->getNumber())
		{
			return;
		}	
		
		if ($track->getCarrierCode() !='ecomexpress')
		{ //die('in');
			return ;
		}	
		$model = $this->_objectManager->create ( 'Ecom\Ecomexpress\Model\Awb' );
		$awbobj = $model->loadByAwb($track->getNumber());
		$awb_data = array();
		$awb_data['status'] = 'Assigned';
		$awb_data['state'] = 1;
		$awb_data['orderid'] = $order->getId();
		$awb_data['shipment_to'] = $order->getShippingAddress()->getName();
		$awb_data['shipment_id'] = $track->getShipment()->getEntityId();
		$awb_data['updated_at'] = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d H:i:s');
		$model->setData($awb_data);
		$model->setId($awbobj);

		$model->save();
		//print_r($track->getNumber());die;
		return;	
	}
}