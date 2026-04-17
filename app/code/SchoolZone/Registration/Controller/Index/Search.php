<?php
namespace SchoolZone\Registration\Controller\Index;

class Search extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $storeManagerInterface;
	protected $_categoryCollection;
	protected $_storeManager;
	protected $jsonFactory;
	protected $resultRedirectFactory;
	protected $_resultLayoutFactory;
	protected $transportBuilder;
	protected $eavAttribute;
    protected $eavConfig;
    // protected $postFactory;

	public function __construct(
		// \SchoolZone\Registration\Model\PostFactory $postFactory,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\Eav\Model\Attribute $eavAttribute,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
		\Magento\Framework\Controller\Result\Redirect $resultRedirectFactory,
		 \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
		\Magento\Store\Model\StoreManagerInterface $StoreManagerInterface,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		// $this->postFactory = $postFactory;
		$this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
		$this->transportBuilder = $transportBuilder;
		$this->_resultLayoutFactory = $resultLayoutFactory;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->jsonFactory = $jsonFactory;
		$this->storeManagerInterface = $StoreManagerInterface;
		$this->_categoryCollection = $categoryCollection;
    	$this->_storeManager = $storeManager;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		// $name = $this->getRequest()->getPost('name');
		$result = $this->jsonFactory->create();
	}
}