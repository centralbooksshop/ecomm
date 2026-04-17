<?php
namespace Cynoinfotech\StorePickup\Observer;

use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Session\Storage;

class OrderSave implements \Magento\Framework\Event\ObserverInterface {

    /** @var \Magento\Framework\Logger\Monolog */
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $helper;
    protected $storepickuporderFactory;
    protected $objectManager = null;
    protected $sessionStorage;
    protected $helperData;
    private $logger;
    protected $storeManager;
   
    public function __construct(
        \Cynoinfotech\StorePickup\Helper\Data $helper,
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory,
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Storage $sessionStorage,    
        OrderFactory $orderFactory,
	ScopeConfigInterface $scopeConfig,
	\Retailinsights\SmsOnOrderStatusChange\Helper\Data $helperData,
	\Psr\Log\LoggerInterface $logger,
	\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storepickuporder = $storepickuporderFactory;
        $this->storepickupFactory = $storepickupFactory;
        $this->objectManager = $objectManager;
        $this->sessionStorage = $sessionStorage;
	$this->_orderFactory = $orderFactory;
	$this->_scopeConfig = $scopeConfig;
	$this->helperData = $helperData;
	$this->logger = $logger;
	$this->_storeManager = $storeManager;
    }

   public function execute(\Magento\Framework\Event\Observer $observer ) {
   //public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order)

	   $orderIds = $observer->getEvent()->getOrderIds();
	   $data = $this->helper->getStorepickupDataFromSession();
	   if(!empty($data['store_pickup'])){
	   $incr_array=array();
	   $this->logger->info(" Store pick up oreder Ids : " . print_r($orderIds, true));
	   $incr_array=array();
	   $custmerName ='';
	   $incrementId ='';
	   if($this->getWebsiteCode() == 'schools') {
	     // if(count($orderIds) > 1){
		 foreach ($orderIds as $key => $value) {
                    $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($value);
                    $incr_array[] = '#'.$order->getIncrementId();
		 }
	     // }
	   }
	   $incrementId = implode(',', $incr_array);	
           $this->logger->info(" order Increment IDs : " . print_r($incr_array, true));
	   $this->logger->info(" order Increment IDs : " . print_r($incrementId, true));
	   $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($orderIds[0]);
	   $custmerName = $order->getShippingAddress()->getData('firstname');
	   $this->logger->info(" custmerName : " . $custmerName);
	   $mobile = $order->getShippingAddress()->getTelephone();
	   $this->logger->info(" mobile : " . $mobile);
	   $store_pickup_id = $data['store_pickup'];
	   $pickupStoreData = $this->getPickupAddressArray($store_pickup_id);
	   $outletname = $pickupStoreData['name'];
	   $outletnumber =  $pickupStoreData['store_phone'];
	   $this->logger->info(" outletname  and outletnumber: " . $outletname." ".$outletnumber);
	   $msgStorepickup = "Dear ".$custmerName.", Greetings! Your Order ".$incrementId." has successfully placed. Please collect your kit from our ".$outletname." store after 48 hours of order placed. Before visiting, kindly call ".$outletnumber." to track your order Write to us at help@centralbooksonline.com. Have a nice day!";
	   $this->logger->info(" msgStorepickup : " . $msgStorepickup);
	   $this->logger->info(" getStorepickupDataFromSession : " . print_r($this->helper->getStorepickupDataFromSession(), true));
	   $sms = $this->helperData->SendSms($msgStorepickup,"Y",$mobile);
	   $this->logger->info(" SMS sent successfully ");
	}
        if (count($orderIds)) {
            $norderId = $orderIds[0];
            $order = $this->_orderFactory->create()->load($norderId);
         }
 

	if(!empty($data['store_pickup'])){
	    $orderIncrementId = $order->getIncrementId();
            $orderId = $order->getId();
            // $orderData = $order->getData();
	    $store_pickup_id = $data['store_pickup'];		
	if (is_array($data) && !($data === null)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $orderObj = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
                // $orderDetails = $orderObj->getData();
		$data['order_id'] = $orderId;			
                $pickup_address = $this->getPickupAddress($store_pickup_id);
                $pickupStoreData = $this->getPickupAddressArray($store_pickup_id);
                $data['pickup_address'] = $pickup_address;
                $data['increment_id'] = $orderIncrementId;
                $data['store_name'] = $pickupStoreData['name'];
                $data['pickup_person_name'] = $data['pickup_person_name'];
                $data['pickup_person_id'] = $data['pickup_person_id'];
                $data['customer_phone'] = $orderObj->getShippingAddress()->getTelephone();
                $data['payment_method'] = $orderObj->getPayment()->getMethodInstance()->getTitle();
                $data['order_status'] = $orderObj->getStatus();
                $data['given_person'] = '';
                $data['delivery_date'] = '';
                $data['store_status'] = '';
		$obj = $this->objectManager->get('\Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder');
		$obj->SavePickupOrder($data);
		$this->sessionStorage->unsData($this->helper->getStorepickupAttributesSessionKey());
	  }			
      }
		return $order;
    }
    
    public function getPickupAddress($id)
    {
        $storepickup = $this->storepickupFactory->create();
        $pickup_address = $storepickup->getCollection()->addFieldToFilter('entity_id', ['eq' => $id]);
        
        $full_address = '';
        if (!empty($pickup_address->getData())) {
            $full_address.= $pickup_address->getData()[0]['name'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_address'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_city'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_state'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_pincode'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_country'] . ', ';
            $full_address.= 'T: ' . $pickup_address->getData()[0]['store_phone'] . ', ';
            $full_address.= 'Email: ' . $pickup_address->getData()[0]['store_email'];
        }
         
        return $full_address;
    }

    public function getPickupAddressArray($id)
    {
        $storepickup = $this->storepickupFactory->create();
        $pickup_address = $storepickup->getCollection()->addFieldToFilter('entity_id', ['eq' => $id]);
        
        $full_address = [];
        if (!empty($pickup_address->getData())) {
            $full_address['name'] = $pickup_address->getData()[0]['name'];
            $full_address['store_address'] = $pickup_address->getData()[0]['store_address'];
            $full_address['store_city'] = $pickup_address->getData()[0]['store_city'];
            $full_address['store_state'] = $pickup_address->getData()[0]['store_state'];
            $full_address['store_pincode'] = $pickup_address->getData()[0]['store_pincode'];
            $full_address['store_country'] = $pickup_address->getData()[0]['store_country'];
            $full_address['store_phone']= $pickup_address->getData()[0]['store_phone'];
            $full_address['store_email']= $pickup_address->getData()[0]['store_email'];
        }  
        return $full_address;
    }

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }

}
