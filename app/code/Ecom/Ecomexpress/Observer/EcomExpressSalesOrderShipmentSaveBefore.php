<?php


namespace Ecom\Ecomexpress\Observer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class EcomExpressSalesOrderShipmentSaveBefore implements ObserverInterface {

	protected $_objectManager;
	
	public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
		RequestInterface $request,
        ManagerInterface $messageManager) 
	{
		$this->request = $request;
        $this->messageManager = $messageManager;
		$this->_objectManager = $objectManager;
	}
	
	public function execute(\Magento\Framework\Event\Observer $observer) {
		$postData = $this->request->getPost();
		if(isset($postData['namespace']) && $postData['namespace']=='sales_order_grid'){
			return;
		}
		if(isset($postData['tracking']) && count($postData['tracking'])>0 && $postData['tracking'][1]['carrier_code']=='ecomexpress') {
		
		//print_r($postData);die('observer');
		if(isset($postData['invoice']) && count($postData['invoice']))
			$items = $postData['invoice']['items'];
		else
			$items = $postData['shipment']['items'];
		

		$invoice = $observer->getEvent()->getInvoice();
		$shipment = $observer->getEvent()->getShipment();
		$order = $shipment->getOrder();
		$shipping_method = $order->getShippingMethod();
		$payment = $order->getPayment()->getMethodInstance()->getCode();
		if(isset($postData['tracking']) && count($postData['tracking'])>0 && $postData['tracking'][1]['carrier_code']=='ecomexpress')
		{
			$awbno = $postData['tracking'][1]['number'];
			$pay_type = 'PPD';
			if($payment == 'cashondelivery' || $payment=='checkmo' || $payment == 'msp_cashondelivery' || $payment == 'phoenix_cashondelivery')
				$pay_type = 'COD';
			
			$response = $this->_objectManager->create('Ecom\Ecomexpress\Model\Automaticawb')->authenticateAwb($order,$pay_type,$awbno,$items);
			
			foreach($response['shipments'] as $res) {
				if(isset($res['success']) && $res['success']==1){
					return true;
				}else{
					$this->messageManager->addError(__($res['reason']));
					throw new \Exception($res['reason'], 1);
				}
			}
			
		}elseif(strpos($shipping_method, 'ecomexpress') !== false){ 		
			$pay_type = 'PPD';
					
			if($payment == 'cashondelivery' || $payment == 'phoenix_cashondelivery' || $payment == 'mst_cashondelivery'){
				$pay_type = 'COD';
			}
			$model = $this->_objectManager->create ( 'Ecom\Ecomexpress\Model\Awb' )->getCollection()
				->addFieldToFilter('state',0)
				->addFieldToFilter('awb_type',$pay_type);			
			if(count($model->getData())>0){

				$awbno = $model->getFirstItem()->getAwb();
				//echo $awbno;die;
				$response = $this->_objectManager->create('Ecom\Ecomexpress\Model\Automaticawb')->authenticateAwb($order,$pay_type,$awbno,$items);
				
				foreach($response['shipments'] as $res) {
					if(isset($res['success']) && $res['success']==1){ 
						$track =$this->_objectManager->get('\Magento\Sales\Model\Order\Shipment\Track')
						->setNumber($awbno)
						->setCarrierCode('ecomexpress')
						->setTitle('ecomexpress');
						$shipment->addTrack($track);
					}
					else { 
						$track = $this->_objectManager->get('\Magento\Sales\Model\Order\Shipment\Track')
						->setNumber($value->awb)
						->setCarrierCode('ecomexpress')
						->setTitle('ecomexpress');
						$shipment->addTrack($track);
					}
				}
			}
			else { 
				$this->messageManager->addError(__('AWB number is not available'));
				throw new \Exception('AWB number is not available', 1);	
			}
		}
	}

	}
}