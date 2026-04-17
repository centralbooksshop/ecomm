<?php
namespace SchoolZone\Customer\Block;
class StudentInfo extends \Magento\Framework\View\Element\Template
{
	protected $postFactory;
	public $_storeManager;
	protected $eavConfig;
	protected $postaddFactory;

	public function __construct(
		\SchoolZone\Customer\Model\ResourceModel\Postadd\CollectionFactory $postaddFactory,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\SchoolZone\Customer\Model\PostFactory $postFactory,
		\Magento\Framework\View\Element\Template\Context $context
	)
	{
		$this->postaddFactory=$postaddFactory;
		$this->eavConfig = $eavConfig;
		$this->_storeManager=$storeManager;
		$this->postFactory=$postFactory;
		parent::__construct($context);
	}
	public function getBaseUrl(){
		return $this->_storeManager->getStore()->getBaseUrl();
	}
	public function getMediaUrl(){
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
	public function sayHello()
	{
		$post = $this->postFactory->create();
		$collection = $post->getCollection();
		return __('Hello World');
	}
	public function getSchoolType(){
		$school_type='';
		$collection = $this->postaddFactory->create()
			->addFieldToSelect('*')
			->addFieldToFilter('school_name', ['in'=>$_SESSION['school_name']]);

		if($collection->getFirstItem()->getData('school_name')){
			$school_type = $collection->getFirstItem()->getData('school_type');
		}
       return $school_type; 

	}

	public function studentData()
	{
		$page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //$startDate = date("Y-m-d h:i:s",strtotime('2026-01-01 00:00:00')); // start date	
        //$endDate = date("Y-m-d h:i:s", strtotime('2032-01-01 12:00:00')); // end date
      	//$pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5000;

		// $post = $this->postFactory->create()->setPageSize($pageSize)->setCurPage($page);
		$collection = $this->postFactory->create()->getCollection()
	    ->addFieldToFilter('school_name', ['in'=>$_SESSION['school_name']]);
		//->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate));
	    //->setPageSize($pageSize)->setCurPage($page);
		
		$attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
        $options = $attribute->getSource()->getAllOptions();
        

		    foreach ($collection as $value) {
			  foreach ($options as $class) {
				if($class['value'] == $value['class']){
					$value->setClass($class['label']);
				}
			  }

		    }

		return $collection;
	}

    protected function _prepareLayout()
	{
	   parent::_prepareLayout();
       $this->pageConfig->getTitle()->set(__('Student Details'));

       if ($this->getOrderCollection()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','vlc.history.pager')->setAvailableLimit(array(10=>10,15=>15,20=>20));
            $pager->setLimit(10)->setShowPerPage(true);
            $pager->setCollection($this->getOrderCollection());
            $this->setChild('pager', $pager);
            $this->getOrderCollection()->load();
        }
	    return $this;
	}
	 public function getPagerHtml(){
        return $this->getChildHtml('pager');
    }
}
?>
