<?php
namespace SchoolZone\Customer\Controller\Index;

class Display extends \Magento\Framework\App\Action\Action
{
	protected $resultRedirectFactory;
	protected $_pageFactory;
	protected $_storeManager;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_storeManager = $storeManager;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
		$school_name_value ='';
		$url_path = 'schoolzone_customer/index/login';
	    $url =$baseUrl.$url_path;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->create('Magento\Customer\Model\Session');

		if(isset($_SESSION['school_name'])) {
			 $default_school_name = $_SESSION['school_name'];
		     $customerSession->setSchoolNameValue($default_school_name);
	         //$school_name_value = $customerSession->getSchoolNameValue();
		     return $this->_pageFactory->create();
		} else {
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath($url);
			return $resultRedirect;
		}

	}
}