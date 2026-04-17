<?php	
namespace SchoolZone\Customer\Block;
class Display extends \Magento\Framework\View\Element\Template
{
	protected $_orderCollectionFactory;
	private $order;
	protected $_customerRepositoryInterface;
	protected $addressRepositoryInterface;
	public $_storeManager;
	protected $eavConfig;
	protected $statusCollectionFactory;
	protected $schoolCollection;
    protected $orderCollection;
	protected $productMap = null;
	public function __construct(
	\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
	\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolCollection,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
	\Magento\Customer\Api\AddressRepositoryInterface  $addressRepositoryInterface,
	\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Sales\Model\Order $order,
	\Magento\Eav\Model\Config $eavConfig,
		\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
	\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory 
	)
	{
		$this->_productCollectionFactory = $productCollectionFactory; 
		$this->schoolCollection = $schoolCollection;
		$this->statusCollectionFactory = $statusCollectionFactory;
		$this->_storeManager=$storeManager;
		$this->addressRepositoryInterface = $addressRepositoryInterface;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->order = $order;
		$this->eavConfig = $eavConfig;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		parent::__construct($context);
	}
	public function getBaseUrl(){
		return $this->_storeManager->getStore()->getBaseUrl();
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
	    

	public function getMediaUrl(){
		return $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
	}
	public function getProduct($entity_id){
		$order=$this->order->load($entity_id);
		
		$products = array();
		foreach ($order->getItems() as $value) {
			if($value['parent_item_id'] ==''){
				$products[] =  $value['name'];
			}
		}
		return $products; 
	}
	public function sayHello()
	{
		$order=$this->order->load(1);
		return $order->getItems();
		return __('Hello World');
	}
	public function getCustomerDetails($customerId){
		$customer = $this->_customerRepositoryInterface->getById($customerId);
		$billingAddressId = $customer->getDefaultBilling();

 		if($billingAddressId){
		$billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
		$telephone = $billingAddress->getTelephone();
		} else {
			$billingAddress = '';
			$telephone = '';
		}
    	
		return $telephone;
	} 
	public function getShippingPhoneNumber($orderId){
          $order = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id', $orderId)->getFirstItem();
	   if ($order && $order->getShippingAddress()) {
             $shippingAddress = $order->getShippingAddress();
             return $shippingAddress->getTelephone();
           }
           return '';		
	}
	public function getShippingName($orderId){
          $order = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id', $orderId)->getFirstItem();
           if ($order && $order->getShippingAddress()) {
             $shippingAddress = $order->getShippingAddress();
             return $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
           }
           return '';
        }
	public function getBillingName($orderId){
		$order = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id', $orderId)->getFirstItem();
             if ($order && $order->getBillingAddress()) {
             $billingAddress = $order->getBillingAddress();
             return $billingAddress->getFirstname().' '.$billingAddress->getLastname();
           }
	  return ''; 
	}
	public function getStoreName($orderId){
		$order = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id', $orderId)->getFirstItem();
		if ($order && $order->getStoreId()) {
                $storeId = $order->getStoreId();
                $store = $this->_storeManager->getStore($storeId);
                $storeName = $store->getName(); 
		return $storeName;
		}
          return '';
        }
   public function getOrderCollection()
   {
    if ($this->orderCollection) {
        return $this->orderCollection;
    }
    $page = $this->getRequest()->getParam('p') ?: 1;
    $startDate = '2026-01-01 00:00:00';
    $endDate   = '2040-01-01 12:00:00';
    $pageSize = $this->getRequest()->getParam('limit') ?: 50;
    $this->orderCollection = $this->_orderCollectionFactory->create()
        ->addFieldToSelect([
            'entity_id',
            'increment_id',
            'created_at',
            'subtotal',
            'grand_total',
            'status',
            'customer_email',
            'customer_firstname',
            'customer_lastname',
            'student_name',
            'roll_no',
            'school_name',
            'store_id'
        ])
        ->addFieldToFilter('main_table.school_id', ['in' => $this->getSchoolNameSessionValue()])
        ->addFieldToFilter('main_table.created_at', ['from' => $startDate, 'to' => $endDate])
        ->addFieldToFilter('main_table.status', ['nin' => ['order_split']]);
    // ✅ Define connection properly
    $connection = $this->orderCollection->getConnection();

    /* Billing Address Join */
    $this->orderCollection->getSelect()->joinLeft(
        ['billing' => $this->orderCollection->getTable('sales_order_address')],
        "main_table.entity_id = billing.parent_id AND billing.address_type = 'billing'",
        [
            'billing_firstname' => 'billing.firstname',
            'billing_lastname'  => 'billing.lastname'
        ]
    );
    /* Shipping Address Join */
    $this->orderCollection->getSelect()->joinLeft(
        ['shipping' => $this->orderCollection->getTable('sales_order_address')],
        "main_table.entity_id = shipping.parent_id AND shipping.address_type = 'shipping'",
        [
            'shipping_firstname' => 'shipping.firstname',
            'shipping_lastname'  => 'shipping.lastname',
            'shipping_telephone' => 'shipping.telephone'
        ]
    );
    $shipmentTable = $connection->getTableName('cbo_assign_shippment');
    $latestShipmentSubQuery = "
        SELECT *
        FROM {$shipmentTable} s1
        WHERE s1.id = (
            SELECT MAX(s2.id)
            FROM {$shipmentTable} s2
            WHERE s2.order_id = s1.order_id
        )
    ";
    $this->orderCollection->getSelect()->joinLeft(
        ['cbo' => new \Zend_Db_Expr("({$latestShipmentSubQuery})")],
        'main_table.entity_id = cbo.order_id',
        [
            'tracking_title'  => 'cbo.tracking_title',
            'tracking_number' => 'cbo.tracking_number',
            'cbo_created_at'  => 'cbo.created_at',
            'driver_id'       => 'cbo.driver_id',
            'deliveryboy_id'  => 'cbo.deliveryboy_id'
        ]
    );
    $this->orderCollection->getSelect()->joinLeft(
        ['driver' => $connection->getTableName('cboshipping_autodrivers')],
        'cbo.driver_id = driver.id',
        ['driver_name' => 'driver.driver_name']
    );

    $this->orderCollection->getSelect()->joinLeft(
        ['delivery' => $connection->getTableName('deliveryboy_deliveryboy')],
        'cbo.deliveryboy_id = delivery.id',
        ['deliveryboy_name' => 'delivery.name']
    );
        $this->orderCollection
            ->setPageSize($pageSize)
            ->setCurPage($page);
        $this->orderCollection->getSelect()->group('main_table.entity_id');
        return $this->orderCollection;
   }
   public function getOrderCollectionCount()
   {
        $startDate = date("Y-m-d h:i:s",strtotime('2026-01-01 00:00:00')); // start date        
        $endDate = date("Y-m-d h:i:s", strtotime('2040-01-01 12:00:00')); // end date
        $collection = $this->_orderCollectionFactory->create()
                 ->addAttributeToSelect('*')
                 ->addFieldToFilter('school_id',['in'=>$this->getSchoolNameSessionValue()])
                 ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
                 ->addFieldToFilter('status', array('nin' => array('order_split')));
                 return $collection->getSize();
    }
   protected function _prepareLayout()
   {
    parent::_prepareLayout();
    $this->pageConfig->getTitle()->set(__('Order Details'));
    $collection = $this->getOrderCollection();
    if ($collection) {
        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'vlaac.history.pager'
        );
        $pager->setAvailableLimit([50 => 50, 100 => 100, 200 => 200]);
        $pager->setShowPerPage(true);
        $pager->setCollection($collection);

        $this->setChild('pager', $pager);
    }
    return $this;
   }
	public function getClasses() {
	  $collection = $this->_productCollectionFactory->create();
      $collection->addAttributeToSelect('*')
       //->addAttributeToFilter('school_name', $this->getSchoolNameSessionValue());
	  ->addFieldToFilter('school_name',['in'=>$this->getSchoolNameSessionValue()]);

		$attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
		$options = $attribute->getSource()->getAllOptions();
		$class = array();
		foreach ($options as $valueOption) {
			foreach ($collection as $key => $value) {
                if($valueOption['value'] == $value->getClassSchool() && $value->getTypeId() == 'bundle'){
					$class[] = $valueOption;
				}
	         }
		}
       $mainclass = array_unique($class, SORT_REGULAR);
	    //echo "<pre>"; print_r($mainclassvalue); echo "</pre>"; die;
		return $mainclass;
	}

	public function getStatus()
	{
		$options = $this->statusCollectionFactory->create()->toOptionArray();     
        return $options;
	}

	public function getSchoolType()
	{
		$type = '';
		$session_school_name = $this->getSchoolNameSessionValue();
		if(isset($session_school_name)){
			$filterData = $this->schoolCollection->create()
					->addFieldToSelect('*')
					//->addFieldToFilter('school_name', $this->getSchoolNameSessionValue());
			        ->addFieldToFilter('school_name',['in'=>$this->getSchoolNameSessionValue()]);
			if($filterData->getFirstItem()->getId()){
				$type = $filterData->getFirstItem()->getData('school_type');
			}
		}
		return $type;
	}
	public function getSchools() 
	{
      //$schoolcoll = explode(",", $school_list);
        $school_list = '';
		$session_school_name = $this->getSchoolNameSessionValue();
		if(isset($session_school_name)){
			$schoolfilterData = $this->schoolCollection->create()
					->addFieldToSelect('*')
					//->addFieldToFilter('school_name', $this->getSchoolNameSessionValue());
			        ->addFieldToFilter('school_name',['in'=>$this->getSchoolNameSessionValue()]);
        }
		return $schoolfilterData;
	}
	public function getAllStores()
	{
		$stores = [];
		foreach ($this->_storeManager->getStores() as $store) {
			$stores[$store->getId()] = $store->getName();
		}
		return $stores;
	}
	public function getProductNamesByOrders($orderIds)
	{
		if ($this->productMap !== null) {
			return $this->productMap;
		}
		$connection = $this->_orderCollectionFactory->create()->getConnection();
		$select = $connection->select()
			->from(
				$connection->getTableName('sales_order_item'),
				['order_id', 'name']
			)
			->where('order_id IN (?)', $orderIds)
			->where('parent_item_id IS NULL');

		$rows = $connection->fetchAll($select);
		$map = [];
		foreach ($rows as $row) {
			$map[$row['order_id']][] = $row['name'];
		}
		$this->productMap = $map;
		return $map;
	}
}
