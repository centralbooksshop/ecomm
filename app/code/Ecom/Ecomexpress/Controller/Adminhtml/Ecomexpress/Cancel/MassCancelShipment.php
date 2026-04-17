<?php


namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Cancel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;

use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;

/**
 * Class MassCancelShipment
 */
class MassCancelShipment extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{	
	/**
	 * @param Context $context
	 * @param Filter $filter
	 * @param CollectionFactory $collectionFactory
	 */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }
    
    /**
     * Cancel selected shipment
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    { 
    	$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
    	if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
	    	$shipment_ids = array();
	    	$params = array();
	    	$type = 'post';
	    	$params['username'] = $configvalue->getValue('carriers/ecomexpress/username');
	        $params['password'] = $configvalue->getValue('carriers/ecomexpress/password');
	        if ($collection->getSize()) {
	            foreach ($collection as $shipment=>$value) {
	            	$shipment_ids[] = $value["entity_id"];  
	            }
	        }
	        if(!count($shipment_ids) || !($params['username']) || !($params['password'])){
				$this->messageManager->addError(__('Kindly fill username and password to track the order(s).'));
				$this->_redirect('sales/shipment/index');
				return;
			}
	        $model = $this->_objectManager->get('Ecom\Ecomexpress\Model\Awb');
	        
	        $track_awb = array();
	        foreach($shipment_ids as $key=>$value){
	        	$ecom_awb = $model->getCollection()->addFieldToFilter('shipment_id',$value)->getData();
				if(count($ecom_awb))
					$track_awb [] = $ecom_awb;
	        }
	        if(!count($track_awb)){
				$this->messageManager->addError(__('Shipment is not created through ECOM'));
				$this->_redirect('sales/shipment/index');
				return;
			}
	        $params_info = array();
	        foreach($track_awb as $key=>$value){
	        	foreach($value as $key=>$value1){
	        		$params_info['awb'][] =  $value1['awb'];
	        	}
	        }
	       	$params['awbs'] =  implode(",",$params_info['awb']);
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
	        	$params_response = array();
	        	foreach($awb_codes as $awb_msg)
	        	{
	        		$params_response['reason'] = $awb_msg->reason;
	        		$params_response['order_number'] = $awb_msg->order_number;
	        		$params_response['awb'] = $awb_msg->awb;     
	        		if($awb_msg->success != 1)
	        		{
	        			$params_response['success'] = 'Canceled Failure';
	        			$this->messageManager->addSuccess(__("Shipment for the order number " .$params_response['order_number'].  " and AWB number " . $params_response['awb']. " is ". $params_response['success']."due to".$params_response['reason']));
	        			echo"<br/>";
	        		}
	        		else{
	        			$params_response['success'] = 'Canceled Successfully';
	        			$this->messageManager->addSuccess(__("Shipment for the order number " .$params_response['order_number'].  " and AWB number " . $params_response['awb']. " is ". $params_response['success']));
	        			echo"<br/>";
	        		}
	        	}
	        	if(empty($awb_codes))
	        	{
	        		$this->messageManager->addError(__('Please add valid Username,Password and AWB in plugin configuration'));
	        	}
	        	/*else{
	        		$this->messageManager->addSuccess(__('Shipment Canceled Successfully'));
	        	}*/
	        }
	        else
	        {
	        	$this->messageManager->addError(__('Shipment is not Canceled'));
	        }
	        $this->_redirect('sales/shipment/index');
    	}
    }
}