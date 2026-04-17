<?php

namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Fetch;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Fetchpincode
 */
class Fetchpincode extends \Magento\Backend\App\Action {

	protected $resultPageFactory;
	
	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
		parent::__construct ( $context );
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute() {
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$params = array();
			$configvalue = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
			$params['username'] = $configvalue->getValue('carriers/ecomexpress/username');
			$params['password'] = $configvalue->getValue('carriers/ecomexpress/password');
			if($configvalue->getValue('carriers/ecomexpress/sanbox')){
				$url = 'https://clbeta.ecomexpress.in/apiv2/pincodes/';
			}
			else {
				$url = 'https://api.ecomexpress.in/apiv2/pincodes/';
			}
			if($params)
			{
				$type = 'post';
				$getpincodes = $this->_objectManager->get('Ecom\Ecomexpress\Helper\Data');
				$retValue = $getpincodes->execute_curl($url, $type, $params);
				if (!$retValue){
					$this->messageManager->addError(__('Ecom service is currently Unavilable , please try after sometime'));
					$this->_redirect('ecomexpress/ecomexpress/pincode');
					return;
				}
				$pin_codes = json_decode($retValue);			
				$sort_pincodes = array();			
				foreach ($pin_codes as $key => $row)
				{	
					$sort_pincodes[$key] = $row->pincode;
				}		
				$delete = $this->_objectManager->get('Ecom\Ecomexpress\Model\Pincode')->delete_pinocdeAll();			
				if (sizeof($sort_pincodes))
				{
					foreach ($pin_codes as $key => $item) {			
						try {
							$model = $this->_objectManager->create ( 'Ecom\Ecomexpress\Model\Pincode' );
							$data = array();
							$data['pincode'] = $item->pincode;
							$data['city'] = $item->city;
							$data['state'] = $item->state;
							$data['dccode'] = $item->dccode;
							$data['city_code'] = $item->city_code;
							$data['state_code'] = $item->state_code;
							$data['date_of_discontinuance'] = $item->date_of_discontinuance;
							$datefinder = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
							$date = $datefinder->date ();
							$data['created_at'] = $date;
							$data['updated_at'] = $date;
							$model->setData($data);
							$model->save();		
						}			 
						catch (\Exception $e)
						{
							echo 'Caught exception: ',  $e->getMessage(), "\n";
						}
					}
				}
				$this->messageManager->addSuccess(__('Pincode Updated Successfully'));
			}
			else{
				$this->messageManager->addError(__('Please add valid Username and Password'));
			}
			$this->_redirect('ecomexpress/ecomexpress/pincode');
		}
    }
}