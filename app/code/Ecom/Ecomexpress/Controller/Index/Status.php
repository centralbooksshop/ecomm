<?php


namespace Ecom\Ecomexpress\Controller\Index;

class Status extends \Magento\Framework\App\Action\Action {
	
	protected $resultPageFactory;
	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct ( $context );
	}	
	public function execute() {
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$params = array ();
			$type = 'post';
			$params ['username'] = $this->getRequest ()->getParams ( 'username' );
			$params ['password'] = $this->getRequest ()->getParams ( 'password' );
			$params ['awb'] = $this->getRequest ()->getParams ( 'awb' );
			$params ['status'] = $this->getRequest ()->getParams ( 'status' );
			$msg = '';
			$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
			if (($params ['username'] != $configvalue->getValue ( 'carriers/ecomexpress/username' )) || ($params ['password'] != $configvalue->getValue ( 'carriers/ecomexpress/password' ))) {
				$msg = "User Authentication is incorrect";
			} 
			else {
				$awb_model = $this->_objectManager->get ( 'Ecom\Ecomexpress\Model\Awb' );
				$model = $awb_model->getCollection ()->addFieldToFilter ( 'state', 1 )->getData ();
				$flag = false;
				foreach ( $model as $val ) {
					if ($val ['awb'] == $params ['awb']) {
						if ($val ['status'] == $params ['status']) {
							$msg = "Status is already Updated";
							$flag = true;
						} else {
							$awb_data = $awb_model->getCollection ()->addFieldToFilter ( 'awb', $val ['awb'] )->getFirstItem ()->getData ();
							$awb_model->load ( $awb_data ['awb_id'] )->setData ( 'status', $params ['status'] );
							$awb_model->save ();
							$flag = true;
							$msg = "Status is Updated Successfully";
							break;
						}
					}
				}
				if ($flag == false)
					$msg = " Wrong AWB Number";
			}
			return $msg;
		}
	}
}