<?php


namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Cancel;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
/**
 * Class Cancel
 */
class Cancel extends \Magento\Backend\App\Action {
	
	/**
	 *
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;
	
	/**
	 *
	 * @param \Magento\Framework\App\Action\Context $context        	
	 * @param
	 *        	\Magento\Framework\View\Result\PageFactory resultPageFactory
	 */
	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
		parent::__construct ( $context );
		$this->resultPageFactory = $resultPageFactory;
	}
	/**
	 * Default Cancel Shipment page
	 *
	 * @return void
	 */
	public function execute() { //die('----');
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$params = array();
			$shipment_ids = $this->getRequest()->getParam('shipment_ids');		
			if($shipment_ids)
			{
				$track_awb = $this->_objectManager->get('Ecom\Ecomexpress\Model\Awb')->getCollection()->addFieldToFilter('shipment_id',$shipment_ids)->getData();
				$type = 'post';
				$params['username'] = $configvalue->getValue('carriers/ecomexpress/username');
				$params['password'] = $configvalue->getValue('carriers/ecomexpress/password');
				$params_info = array();				
				foreach($track_awb as $awb){
					$params_info['awb'][] = $awb['awb'];
				}			
				$params['awbs'] = implode(",",$params_info['awb']);
				if($configvalue->getValue('carriers/ecomexpress/sanbox') ==1){
					$url = 'https://clbeta.ecomexpress.in/apiv2/cancel_awb/';
				}
				else {
					$url = 'https://api.ecomexpress.in/apiv2/cancel_awb/';
				}		
				if($params)
				{
					$helper = $this->_objectManager->get('Ecom\Ecomexpress\Helper\Data');
					$retValue = $helper->execute_curl($url,$type,$params);
					if (!$retValue){
						$this->messageManager->addError(__('Ecom service is currently Unavilable , please try after sometime'));
						$this->_redirect('sales/shipment/index');
						return;
					}
					$awb_codes = json_decode($retValue);
					$params_response =array();
					foreach ($awb_codes as $awb_msg)
					{
						$params_response['reason'] = $awb_msg->reason;
						$params_response['order_number'] = $awb_msg->order_number;
						$params_response['awb'] = $awb_msg->awb;						
						if ($awb_msg->success != 1)
						{
							$params_response['success'] = 'Canceled Failure';
							$this->messageManager->addSuccess(__("Shipment for the order number " .$params_response['order_number'].  " and AWB number " . $params_response['awb']. " is ". $params_response['success']."due to".$params_response['reason']));
							echo "<br/>";
						}
						else{
							$params_response['success'] = 'Canceled Successfully';
							$this->messageManager->addSuccess(__("Shipment for the order number " .$params_response['order_number'].  " and AWB number " . $params_response['awb']. " is ". $params_response['success']));
							echo "<br/>";
						}
					}
					if (empty($awb_codes))
					{
						$this->messageManager->addError(__('Please add valid Username,Password and AWB in plugin configuration'));
					}
					/*else{
						$this->messageManager->addSuccess(__('Shipment Canceled Successfully'));
					}*/
				}
			}
			else
			{
				$this->messageManager->addError(__('Shipment is not Canceled'));
			}
			$this->_redirect('sales/shipment/index');
		}
		
    }
}