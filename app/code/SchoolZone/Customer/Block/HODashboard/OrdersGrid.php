<?php
namespace SchoolZone\Customer\Block\HODashboard;
class OrdersGrid extends \Magento\Framework\View\Element\Template
{
	protected $_orderCollectionFactory;
	private $order;
	protected $_customerRepositoryInterface;
	protected $addressRepositoryInterface;
	public $_storeManager;
	protected $eavConfig;
	protected $statusCollectionFactory;
	protected $schoolCollection;

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

	public function getCurrentStatus(){
		$params = $this->getRequest()->getParams();
		return $params;
	} 

	public function getSchoolOrderCollection()
	{
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$timezoneInterface = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		//$endDate = $timezoneInterface->date()->format('Y-m-d h:i:s');
	    $startDate = date("Y-m-d h:i:s",strtotime('2026-01-01 00:00:00')); // start date	
        $endDate = date("Y-m-d h:i:s", strtotime('2032-01-01 23:59:59')); // end date
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
	    $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 10;
        $collection = $this->_orderCollectionFactory->create()->addAttributeToSelect('*');
		//$collection->addFieldToFilter('school_id', $this->getSchoolNameSessionValue());
		$collection->addFieldToFilter('school_id',['in'=>$this->getSchoolNameSessionValue()]);
		$collection->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate));
	    //$collection->setPageSize($pageSize)->setCurPage($page);
        //echo count($collection);
	$params = $this->getRequest()->getParams();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $highestTime = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('schooldashboard/ordertime/highest_orders_time');
        foreach ($params as $key => $value) {
			if ($key == 'payment_method') {
				$collection->getSelect()->join(["sop" => "sales_order_payment"], 'main_table.entity_id = sop.parent_id',["sop.method"])->where('sop.method = ?', $value);
			} elseif($key == 'time') {
		        if ($value == 'below-3') {
		            $date = (new \DateTime())->modify('-72 hours');
		            $collection->addFieldToFilter('created_at', ['gteq' => $date->format('Y-m-d h:i:s')]);
		        } elseif ($value == 'above-3') {
		            //$date1 = (new \DateTime())->modify('-72 hours');
					$date1 = (new \DateTime())->modify($highestTime);
		            $date2 = (new \DateTime())->modify('-72 hours');
		            $collection->addFieldToFilter('created_at', ['gt' => $date1->format('Y-m-d h:i:s')]);
		            $collection->addFieldToFilter('created_at', ['lteq' => $date2->format('Y-m-d h:i:s')]);
		        }
			} else {
				if ($key != 'p') {
				 $collection->addFieldToFilter($key, $value);
				 //echo count($collection);
				}
			}
		}
		if (!array_key_exists('time', $params)) {
            //$date = (new \DateTime())->modify('-7 days');
			$date = (new \DateTime())->modify($highestTime);
            $collection->addFieldToFilter('created_at', ['gteq' => $date->format('Y-m-d h:i:s')]);
		}
		$collection->addFieldToFilter('status', array('nin' => array('order_split')));
		//echo $collection->getSelect()->__toString();die;
		 //echo '<pre>';print_r($collection->getData());die;
	    return $collection;
    }


	
    public function getSchoolOrderCollectionnew()
    {
        $page     = (int)($this->getRequest()->getParam('p') ?? 1);
        $pageSize = (int)($this->getRequest()->getParam('limit') ?? 10);
        $params   = $this->getRequest()->getParams();

        $startDate = '2026-01-01 00:00:00';
        $endDate   = '2032-01-01 23:59:59';

        $collection = $this->_orderCollectionFactory->create();

        /* SELECT ONLY REQUIRED FIELDS */
        $collection->addFieldToSelect([
            'entity_id',
            'increment_id',
            'store_id',
            'status',
            'created_at',
            'grand_total',
            'customer_id'
        ]);

        /* SCHOOL FILTER */
        if ($this->getSchoolIds()) {
            $collection->addFieldToFilter('school_id', ['in' => $this->getSchoolIds()]);
        }

        /* DATE FILTER */
        $collection->addFieldToFilter('created_at', [
            'from' => $startDate,
            'to'   => $endDate
        ]);

        /* EXCLUDE SPLIT ORDERS */
        $collection->addFieldToFilter('status', ['nin' => ['order_split']]);

        /* PAYMENT METHOD FILTER */
        if (!empty($params['payment_method'])) {
            $collection->getSelect()->join(
                ['sop' => $collection->getTable('sales_order_payment')],
                'main_table.entity_id = sop.parent_id',
                ['payment_method' => 'method']
            )->where('sop.method = ?', $params['payment_method']);
        }

        /* SHIPPING ADDRESS JOIN */
        $collection->getSelect()->joinLeft(
            ['soa' => $collection->getTable('sales_order_address')],
            "main_table.entity_id = soa.parent_id AND soa.address_type = 'shipping'",
            [
                'shipping_phone' => 'telephone',
                'shipping_name'  => "CONCAT(soa.firstname,' ',soa.lastname)"
            ]
        );

        /* BILLING ADDRESS JOIN */
        $collection->getSelect()->joinLeft(
            ['boa' => $collection->getTable('sales_order_address')],
            "main_table.entity_id = boa.parent_id AND boa.address_type = 'billing'",
            [
                'billing_name' => "CONCAT(boa.firstname,' ',boa.lastname)"
            ]
        );

        /* STORE NAME JOIN */
        $collection->getSelect()->joinLeft(
            ['store' => $collection->getTable('store')],
            'main_table.store_id = store.store_id',
            ['store_name' => 'name']
        );

        /* TIME FILTER */
        if (!empty($params['time'])) {
            $date72 = (new \DateTime())->modify('-72 hours')->format('Y-m-d H:i:s');

            if ($params['time'] === 'below-3') {
                $collection->addFieldToFilter('created_at', ['gteq' => $date72]);
            } elseif ($params['time'] === 'above-3') {
                $collection->addFieldToFilter('created_at', ['lteq' => $date72]);
            }
        }

        /* PAGINATION (CRITICAL) */
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    protected function _prepareLayout()
	{
	    parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Order Details'));

        /*if ($this->getSchoolOrderCollection()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','vlc.history.pager')->setAvailableLimit(array(10=>10,15=>15,20=>20));
            $pager->setLimit(10)->setShowPerPage(true);
            $pager->setCollection($this->getSchoolOrderCollection());
            $this->setChild('pager', $pager);
            $this->getSchoolOrderCollection()->load();
        }*/
	    return $this;
	}
	 public function getPagerHtml(){
        //return $this->getChildHtml('pager');
	}
	
	public function getClasses() {
	  $collection = $this->_productCollectionFactory->create();
      $collection->addAttributeToSelect('*')
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
}
