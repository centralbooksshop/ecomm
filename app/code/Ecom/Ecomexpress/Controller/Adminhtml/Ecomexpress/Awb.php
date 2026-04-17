<?php


namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
/**
 * Class Awb
 */
class Awb extends \Magento\Backend\App\Action {
	
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
	 * Default Ecomexpress AWB page
	 *
	 * @return void
	 */
	public function execute() {
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$resultPage = $this->resultPageFactory->create ();
			$resultPage->getConfig()->getTitle()->prepend(__('Ecomexpress AWB Manager'));
	        return $resultPage;
		}
    }
}