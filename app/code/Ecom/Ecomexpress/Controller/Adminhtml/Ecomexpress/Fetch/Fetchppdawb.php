<?php

namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Fetch;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Fetchppdawb extends \Magento\Backend\App\Action {
	
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
	 * Default customer account page
	 *
	 * @return void
	 */
	public function execute() {
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$params = array();
			$params['type'] = "PPD";
			$model = $this->_objectManager->get('Ecom\Ecomexpress\Model\Awb');
			$count = $model->getCollection()->addFieldToFilter('awb_type',$params['type'])->addFieldToFilter('state',0)->getData();
			$configvalue=$this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
			$fetch_awb = $configvalue->getValue('carriers/ecomexpress/fetch_awb');
			$max_fetch_awb = $configvalue->getValue('carriers/ecomexpress/max_fetch_awb');				
				if ($fetch_awb <= $max_fetch_awb) {		
					$type = 'post';
					$params['username'] = $configvalue->getValue('carriers/ecomexpress/username');
					$params['password'] = $configvalue->getValue('carriers/ecomexpress/password');
					$params['count'] = $fetch_awb;
					if($configvalue->getValue('carriers/ecomexpress/sanbox')==1){
						$url = 'https://clbeta.ecomexpress.in/apiv2/fetch_awb/';
					}
					else {
						$url = 'https://api.ecomexpress.in/apiv2/fetch_awb/';
					}					
					if ($params)
					{
						$helper = $this->_objectManager->get('Ecom\Ecomexpress\Helper\Data');
						$retValue = $helper->execute_curl($url,$type,$params);
						if (!$retValue){
							$this->messageManager->addError(__('Ecom service is currently Unavilable , please try after sometime'));
							$this->_redirect('ecomexpress/ecomexpress/awb');
							return;
						}
						$awb_codes = json_decode($retValue);
						if (empty($awb_codes))
						{
							$this->messageManager->addError( __('Please add valid Username,Password and Count in plugin configuration') );
						}		
						foreach ($awb_codes->awb as  $key => $item)
						{
							try {
								$data = array();
								$data['awb'] = $item;
								$data['state'] = 0;
								$data['awb_type'] = $params['type'];
								$datefinder=$this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
								$date = $datefinder->date ();
								$data['created_at'] = $date;
								$data['updated_at'] = $date;
								$model->setData($data);
								$model->save();
							}
							catch (\Exception $e)
							{
								echo 'Caught exception: ',  $e->getMessage(), "\n";
								$this->messageManager->addError($e->getMessage());
							}
						}		
						$this->messageManager->addSuccess(__('AWB Downloaded Successfully'));
					}
					else
					{
						$this->messageManager->addError(__('Please add valid Username,Password and Count in plugin configuration'));
					}
					$this->_redirect('ecomexpress/ecomexpress/awb');			
			}
		}
    }
}