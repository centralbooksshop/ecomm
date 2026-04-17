<?php
namespace SchoolZone\Search\Controller\Index;


class SearchProduct extends \Magento\Framework\App\Action\Action
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
    protected $postFactory;
    protected $postlistFactory;
    protected $resultFactory;


	public function __construct(
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\SchoolZone\Search\Model\PostFactory $postFactory,
		\SchoolZone\Search\Model\PostlistFactory $postlistFactory,
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
		$this->resultFactory = $resultFactory; 
		$this->postlistFactory = $postlistFactory;
		$this->postFactory = $postFactory;
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
		$school_name = $this->getRequest()->getPost('school_name');
		$username = $this->getRequest()->getPost('username');
		$password = $this->getRequest()->getPost('password');
		$roll_numbers = $this->getRequest()->getPost('roll_numbers');
	    $url ="/schools/schoolzone_search/index/display?schoolname=$school_name";
        echo $url;
	}
}