<?php
namespace SchoolZone\Customer\Controller\HODashboard;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
   protected $_pageFactory;
	protected $_storeManager;
	
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_storeManager = $storeManager;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
		$url_path = 'schoolzone_customer/index/login';
	    $url =$baseUrl.$url_path;

		if(isset($_SESSION["school_name"])){
				return $this->_pageFactory->create();
		}else{
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath($url);
			return $resultRedirect;
		}
	}
}
