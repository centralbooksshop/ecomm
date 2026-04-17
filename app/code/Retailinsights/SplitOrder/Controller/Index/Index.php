<?php
namespace Retailinsights\SplitOrder\Controller\Index;


class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $helperData;
	protected $logger;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Retailinsights\SplitOrder\Helper\Data $helperData,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\OrderFactory $orderModel,
		\Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Contact\Model\ConfigInterface $contactConfig)
	{
		$this->_pageFactory = $pageFactory;
		$this->helperData = $helperData;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderModel = $orderModel;
		$this->logger = $logger;
        $this->resource = $resource;
        $this->orderManagement = $orderManagement;
        $this->contactConfig = $contactConfig;
		return parent::__construct($context);
	}

	public function execute()
	{
		
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderData=array();
        $this->logger->info("Currently this working");
		$orderManagement = $objectManager->get('Magento\Sales\Api\OrderManagementInterface');
		$storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$storeID       = $storeManager->getStore()->getStoreId(); 
		$storeCode       = $storeManager->getStore()->getCode(); 
		$storeName     = $storeManager->getStore()->getName();
		$this->logger->info("Current Store :".$storeName."_storeCode_:".$storeCode."_StoreId :".$storeID);


//  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
// $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
// $connection = $resource->getConnection();

// //*****************loading Customer session *****************//
// $customerSession = $objectManager->create('Magento\Customer\Model\Session');

// //******** Checking whether customer is logged in or not ********//
// if ($customerSession->isLoggedIn()) {
// $customer_email = $customerSession->getCustomer()->getId();

// // ***********Getting order collection using customer email id ***********//
//  $order_collection = $objectManager->create('Magento\Sales\Model\Order')->getCollection()->addAttributeToFilter('customer_id', $customer_email);

//  //echo "<pre>";print_r($order_collection->getData());
//  foreach ($order_collection as $order){ 
//     echo "Order Id: ".$order->getEntityId(); 
//     echo "Customer Id: ".$order->getCustomerId(); 
//    } 
// }

//exit();
           
 try {    
          // $main_order_id = $session->getLastOrderId();

          $main_order_id = 656;

           $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $helperData = $objectManager->get('Retailinsights\SplitOrder\Helper\Data');
           $helperData->statusChangeInvoiceGenarate($main_order_id);
          // $main_order_id = $orderIds[0];
          // $this->logger->info("Currently id".json_encode($orderIds));
           $order = $objectManager->create('Magento\Sales\Model\Order')->load($main_order_id);
            $orderItems = $order->getAllVisibleItems();
           $produtcount = count($orderItems);
            $product_ids=array();
            $increment_ids=array();
            $order_ids=array();
            $last_quote_id = array();
            $last_order_id = array();
            $last_increment_id =array();
            $last_status = array();
         foreach ($orderItems as $item) {
         $productId =  $item->getId();
            $product_ids[] = array($productId => $item->getQtyOrdered());
     }
$status = 'fail';
if($produtcount > 1 && $storeCode == 'schools' ) {
    $this->logger->info('Product ids'.json_encode($product_ids));
   foreach ($product_ids as $value) {
    $this->logger->info('Product pass'.json_encode($value));
          $order_details1 = $this->getAllDetailsOne($main_order_id, $value);
          $order1 = $helperData->createMageOrder($order_details1);
           if (isset($order1['error'])) {
                        throw new \Exception($order1['msg']);
                         $this->logger->info('Order error'.$order1['msg']);
           } else {
             $status = 'success';
             // $increment_ids[]=$order1['increment_id'];
              $order_ids[$order1['order_id']]= $order1['increment_id'];
              $last_quote_id[] = $order1['quote_id'];
              $last_order_id[] = $order1['order_id'];
              $last_status[] = $order1['status'];
              $last_increment_id[] = $order1['increment_id'];
             $this->logger->info('Success');
           }
           //exit();
     } 
     if($status == 'success') {
        // $orderManagement->cancel($main_order_id);
       // $order->setStatus("order_split");
        $order->setState("processing")->setStatus("order_split");
        $order->save();
        $checkoutsession = $objectManager->get('Magento\Checkout\Model\Session');
        $checkoutsession->setLastQuoteId(end($last_quote_id));
        $checkoutsession->setLastSuccessQuoteId(end($last_quote_id));
        $checkoutsession->setLastOrderId(end($last_order_id));
        $checkoutsession->setLastRealOrderId(end($last_increment_id));
        $checkoutsession->setLastOrderStatus(end($last_status));
        $checkoutsession->setOrderIds($order_ids);
        $this->logger->info("Last Order Id - ".$last_increment_id[0]);
        //print_r($order_ids);
               
     } 
}
        } catch (\Exception $e) {
           $this->logger->info($e->getMessage());
        }
}

     /**
     * Returns details of order.
     *
     * @param int   $order_id order_id
     * @param array $array    order_qty_detail_array
     *
     * @return array
     */
    public function getAllDetailsOne($order_id, $array)
    {
        $this->logger->info('getPerticularDetails'); // Simple Text Log
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('sales_order_item');

		$order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);

		$orderDetails = [];
		$orderDetails['currency_code'] = $order->getOrderCurrencyCode();
		$orderDetails['order_id'] = $order_id;
		$orderDetails['order_status'] = $order->getStatus();
		$orderDetails['store_id'] = $order->getStoreId();
		$orderDetails['email'] = $order->getCustomerEmail();
		$firstname= $order->getShippingAddress()->getFirstname();
		$lastname= $order->getShippingAddress()->getLastname();
		$street = $order->getShippingAddress()->getStreet();
		$city = $order->getShippingAddress()->getCity();
		$region = $order->getShippingAddress()->getRegion();
		$postcode = $order->getShippingAddress()->getPostcode();
		$telephone = $order->getShippingAddress()->getTelephone();
         $addressData=[
'firstname' => $firstname, //address Details
'lastname' => $lastname,
'street' => $street,
'city' => $city,
'country_id' => $order->getShippingAddress()->getCountryId(),
'region' => $region, // replace with region
'postcode' => $postcode, // replace with real zip code
'telephone' => $telephone,
'save_in_address_book' => 0 // If you want to save in address book
];
       $orderDetails['billing_address'] = $addressData;
        $orderDetails['shipping_address'] = $addressData;
         $this->logger->info('shipping address'.json_encode($orderDetails['shipping_address']));
        $orderDetails['shipping_method'] = $order->getShipping_method() ? $order->getShipping_method() : null;
         $this->logger->info('shipping method'.$orderDetails['shipping_method']);
        $orderDetails['shipping_amount'] = $order->getShippingAmount() ? $order->getShippingAmount() / $order->getTotalQtyOrdered() : null;
        $orderDetails['payment_method'] = $order->getPayment()->getMethod();
        $orderDetails['discount_description'] = $order->getDiscountDescription() ? $order->getDiscountDescription() : null;
        $orderDetails['coupon_code'] = $order->getCouponCode() ? $order->getCouponCode() : null;
        $orderDetails['coupon_rule_name'] = $order->getCouponRuleName() ? $order->getCouponRuleName() : null;
        $orderDetails['order_increment_id'] = $order->getIncrementId();
        $orderDetails['remote_ip'] = $order->getRemote_ip();

        //get all items of order
        $orderItems = $order->getAllVisibleItems();
        $i = 0;

        foreach ($orderItems as $item) {
            if (isset($array[$item->getItem_id()])) {
                //get product data
                $sql1 =  $connection->select()->from(['main_table' => $tableName])->where('main_table.parent_item_id = ?', $item->getItem_id());
                $result1 = $connection->fetchAll($sql1);
                if (!empty($result1)) {
                    //for configurable/bundle products
                    $option_arr = json_decode($result1[0]['product_options'], true);
                    if (isset($option_arr['info_buyRequest']['product'])) {
                        $orderDetails['items'][$i]['product_id'] = $option_arr['info_buyRequest']['product'];
                    } else {
                        $sql12 = $connection->select()->from(['main_table' => $tableName], ['product_id'])->where('main_table.item_id = ?', $result1[0]['parent_item_id']);
                        $result12 = $connection->fetchAll($sql12);
                        $orderDetails['items'][$i]['product_id'] = $result12[0]['product_id'];
                    }
                    if (isset($option_arr['info_buyRequest']['super_attribute'])) {
                        $orderDetails['items'][$i]['product_options']['super_attribute'] = $option_arr['info_buyRequest']['super_attribute'];
                    }
                    if (isset($option_arr['info_buyRequest']['bundle_option'])) {
                        $orderDetails['items'][$i]['product_options']['bundle_option'] = $option_arr['info_buyRequest']['bundle_option'];
                    }
                    if (isset($option_arr['info_buyRequest']['bundle_option_qty'])) {
                        $orderDetails['items'][$i]['product_options']['bundle_option_qty'] = $option_arr['info_buyRequest']['bundle_option_qty'];
                    }
                    if (isset($option_arr['bundle_selection_attributes'])) {
                        $orderDetails['items'][$i]['product_options']['bundle_selection_attributes'] = $option_arr['bundle_selection_attributes'];
                    }
                } else {
                    $sql2 =  $connection->select()->from(['main_table' => $tableName])->where('main_table.item_id = ?', $item->getItem_id());
                    $result2 = $connection->fetchAll($sql2);
                    if (!empty($result2)) {
                        //for downloadable products
                        $option_arr = json_decode($result2[0]['product_options'], true);
                        if (isset($option_arr['links'])) {
                            $orderDetails['items'][$i]['product_options']['links'] = $option_arr['links'];
                        }
                    }
                    $orderDetails['items'][$i]['product_id'] = $item->getProduct_id();
                }
                $orderDetails['items'][$i]['price'] = $item->getPrice();
                $orderDetails['items'][$i]['original_price'] = $item->getOriginalPrice();
                $orderDetails['items'][$i]['qty'] = (int)$array[$item->getItem_id()];
                $orderDetails['items'][$i]['applied_rule_ids'] = $item->getAppliedRuleIds();
                $orderDetails['items'][$i]['discount_percent'] = $item->getDiscountPercent();
                $orderDetails['items'][$i]['discount_amount'] = ($item->getDiscountAmount() / $item->getQtyOrdered()) * (int)$array[$item->getItem_id()];
                $orderDetails['items'][$i]['tax_percent'] = $item->getTaxPercent();
                $orderDetails['items'][$i]['tax_amount'] = ($item->getTaxAmount() / $item->getQtyOrdered()) * (int)$array[$item->getItem_id()];
                $i++;
            }
        }
        return $orderDetails;
    }
}