<?php
namespace SchoolZone\Customer\Block;
class Add extends \Magento\Framework\View\Element\Template
{
	protected $_orderCollectionFactory;
	protected $postaddFactory;
	protected $eavAttribute;
	protected $eavConfig;
	protected $postFactory;

	public function __construct(
	\SchoolZone\Customer\Model\PostFactory $postFactory,
	\Magento\Eav\Model\Config $eavConfig,
	\Magento\Eav\Model\Attribute $eavAttribute,
	\SchoolZone\Customer\Model\PostaddFactory $postaddFactory,
	\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory 
	)
	{
		$this->postFactory=$postFactory;
		$this->eavConfig = $eavConfig;
		$this->eavAttribute = $eavAttribute;
		$this->postaddFactory=$postaddFactory;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		parent::__construct($context);
	}

	public function sayHello()
	{
		return __('Hello World');
	}

	public function getSchools()
	{
		$post = $this->postaddFactory->create();
		$schoolcollection = $post->getCollection();
		//echo $collection->getSelect()->__toString();
		//echo $_SESSION["school_name"];

		/*$school_data = array();
		$modeldata = [];
		foreach ($schoolcollection as $key => $value) {
			$school_namearray = explode(',', $_SESSION["school_name"]);
			if(is_array($school_namearray)) {
				if (in_array($value['school_name'], $school_namearray)) {
					//if($_SESSION["school_name"] == $value['school_name']){
						$modeldata[]['school_name'] = $value['school_name']; 
						$modeldata[]['school_type'] = $value['school_type']; 
						$modeldata[]['school_name_text'] = $value['school_name_text'];
					//}
				}
			}
		 }*/
		//array_push($school_data, $modeldata);
        return $schoolcollection;
		//echo '<pre>';print_r($school_data);die;

	}

	public function getSchoolNameSessionValue()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->create('Magento\Customer\Model\Session');
		//$school_name = $_SESSION['school_name'];
		//$customerSession->setSchoolNameValue($school_name);
	    $school_name = $customerSession->getSchoolNameValue();
		return $school_name;
	}

	public function getOptionClass(){
    $attributeCollection = $this->eavAttribute->getCollection();

    $attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
    $options = $attribute->getSource()->getAllOptions();

        $arr = array();
         foreach ($options as  $value) {
             $arr[]=['value' => $value['value'], 'label' => $value['label']];
         }
        return $arr;
    }
    public function studentData()
	{
		$page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;

      	$pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 10;

		// $post = $this->postFactory->create()->setPageSize($pageSize)->setCurPage($page);
		$collection = $this->postFactory->create()->getCollection()->addFieldToFilter('school_name', ['in'=>$_SESSION['school_name']]);
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
}
?>
