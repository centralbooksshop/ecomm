<?php

namespace Retailinsights\Backorders\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
	
    protected $postCollection;
	protected $productRepository;
    protected $backordersFactory;
    protected $scopeConfig;

	public function __construct(
        \Retailinsights\Orderexport\Model\ResourceModel\Post\CollectionFactory $postCollection,
        Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
         \Magento\Quote\Model\QuoteManagement $quoteManagement,
         \Retailinsights\Backorders\Model\BackordersFactory $backordersFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
          \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order $order
       
    )
    {
        $this->postCollection = $postCollection;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->quote = $quote;
        $this->scopeConfig = $scopeConfig;
        $this->backordersFactory = $backordersFactory;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->order = $order;
        parent::__construct($context);
	}
	

	public function isBackordred($item)
    {    $isbackordred = false;
         $productId =  $item->getProductId();
         $product = $this->productRepository->getById($productId);
         $productStock = $product->getExtensionAttributes()->getStockItem();
         $obj = \Magento\Framework\App\ObjectManager::getInstance();  
         $stockRegistry = $obj->get('Magento\CatalogInventory\Api\StockRegistryInterface');
         $getSalable = $obj->get('Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
		 if($product->getTypeId() != 'bundle') {
            if($product->getTypeId() != 'configurable') {
		       $salable = $getSalable->execute($product->getSku());
               $stockitem = $stockRegistry->getStockItem($product->getId(),$product->getStore()->getWebsiteId());
             }
         }

        if($product->getTypeId() == 'bundle') {
            $_SESSION["bundle_id"] = $product->getId();
        } else { $_SESSION["bundle_id"] = ''; }
        $willBeGiven = '';
         if($product->getTypeId() == 'simple'){
            $school_given_collection = $this->postCollection->create()
                                        ->addFieldToFilter('parent_product_id', $_SESSION["bundle_id"]);

                foreach ($school_given_collection as $key => $schoolData) {
                    if($schoolData->getProductId() == $product->getId()){
                        $optinal_given = $schoolData->getCustomField();
                        if($optinal_given == 1){
                           $willBeGiven = 1;
                        }
                    }
                }
           }



         if($product->getTypeId() == 'simple' && $stockitem['backorders'] == 2 && $product->getQty() <= 0 && $salable[0]['qty'] < 0) {
            $isbackordred = true;
            return $isbackordred;
         }
        if($willBeGiven == 1){
          $isbackordred = true;
        }
		return $isbackordred;
    }

    public function setBackorderData($orderData){
       $return = 'invalid';
       if(count($orderData) > 0) {
        $increment_id = $orderData['order_id'];
        $order_check = explode("-",$increment_id);
        if(count($order_check) <= 1){
            $backorder = $this->backordersFactory->create();
          $backorder->addData($orderData);
        $saveData = $backorder->save();
        if($saveData) {
          $return ='success';
        } else {
          $return ='failure';
         } 
        }
        }
        return $return;
    }

      public function udateBackorderData($increment_id, $orderData){
       $return = 'invalid';
       if(count($orderData) > 0 && isset($increment_id)) {
         $backorder = $this->backordersFactory->create()->load($increment_id, 'order_id');
         $backorder->setBackOrderId($orderData['back_order_id']);
        $backorder->setStatus($orderData['status']);
        $saveData = $backorder->save();
        if($saveData) {
          $return ='success';
        } else {
          $return ='failure';
         } 
        }
        return $return;
    }

    public function isBackOrderCreated($increment_id) {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$tableName = $resource->getTableName('backorder_items');
$status = 'Created';
    $sql        = "SELECT back_order_id FROM " . $tableName . " WHERE order_id = ? and status = ?";
    return $connection->fetchOne($sql, [$increment_id,$status]);
         
    }

    public function isBackOrderEntry($increment_id) {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$tableName = $resource->getTableName('backorder_items');
    $sql        = "SELECT id FROM " . $tableName . " WHERE order_id = ?";
    return $connection->fetchOne($sql, [$increment_id]);
         
    }

    public function getBackOrderMessage($type) {
     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

     return $this->scopeConfig->getValue('backorder/general/'.$type, $storeScope);

    }

    public function getAllBackorderItems($orderIds) {
        $product_names = array();
        foreach ($orderIds as $increment_id) {
              $order = $this->order->loadByIncrementId($increment_id);
                 $orderItems = $order->getAllItems();
                  foreach ($orderItems as $item) {
         if($this->isBackordred($item)){
          $product_names[] = $item->getName();
         }
     }
}
return $product_names;
    }

    public function getBackOrderItemData($increment_id) {
    $order = $this->order->loadByIncrementId($increment_id);
     $orderItems = $order->getAllItems();
     $order_status ='';
     $back_order_data = array();
     $product_skus = array();
     $product_qtys = array();
    foreach ($orderItems as $item) {
         if($this->isBackordred($item)){
          $order_status = 'Yes';
          $product_skus[] = $item->getSku();
          $product_qtys[] = $item->getQtyOrdered();
         }
     }
     if($order_status == 'Yes') {
       $back_order_data['order_id'] = $order->getIncrementId();
       $back_order_data['item_id']  = $order->getEntityId();
       $back_order_data['sku']  = implode(",", $product_skus);
       $back_order_data['qty_ordered']  = implode(",", $product_qtys);
     }

     return $back_order_data;
    }

      public function createBackOrder($orderData) {


        try{
        $store=$this->_storeManager->getStore($orderData['store_id']);
        $store_id = $store->getStoreId();
        $websiteId = $this->_storeManager->getStore($orderData['store_id'])->getWebsiteId();
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);// load customet by email address
        if(!$customer->getEntityId()){
            //If not avilable then create this customer 
            $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($orderData['shipping_address']['firstname'])
                    ->setLastname($orderData['shipping_address']['lastname'])
                    ->setEmail($orderData['email']) 
                    ->setPassword($orderData['email']);
            $customer->save();
        }
        
        $quote=$this->quote->create();
        $obj = \Magento\Framework\App\ObjectManager::getInstance();  
         $checkoutsession = $obj->get('Magento\Checkout\Model\Session');
        $checkoutsession->replaceQuote($quote);
        $quote->setStore($store);
        $quote->setStoreId($store_id);
        // if you have allready buyer id then you can load customer directly 
        $customer= $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer
        //add items in quote
        foreach($orderData['items'] as $item){
            $product = $this->_product->create()->load($item['product_id']);
           $product->setPrice(0);
          $quote->addProduct($product, intval($item['qty']));
        }
        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);
        // Collect Rates and Set Shipping & Payment Method

        $shippingAddress=$quote->getShippingAddress();
        // $shippingAddress->setFreeShipping(true); 
        $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod($orderData['shipping_method']); //shipping method
        $quote->setPaymentMethod($orderData['payment_method']); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory
        $quote->setReservedOrderId($orderData['increment_id'].'-1');
        $quote->save();
        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' =>$orderData['payment_method']]);
         //Now Save quote and your quote is ready
        $quote->setShippingAmount(0);
   $quote->setBaseShippingAmount(0);
   $quote->setGrandTotal(0);
   $quote->setBaseGrandTotal(0);
        // Collect Totals
        $quote->collectTotals()->save();
        
      $order = $this->quoteManagement->submit($quote);

        $order->setShippingAmount(0);
   $order->setBaseShippingAmount(0);
   $order->setGrandTotal(0);
   $order->setBaseGrandTotal(0);
   $order->save();
      $orderId = $order->getIncrementId();
      $order_redirect_id = $order->getEntityId();
        if($orderId){
            $result['increment_id']=  $orderId;
            $result['order_id']=  $order_redirect_id;
             $updateData=[
'status' => 'Created', //address Details
'back_order_id' => $orderId
];

if($this->isBackOrderEntry($orderData['increment_id'])) {
    $this->udateBackorderData($orderData['increment_id'], $updateData);
} else {
    $addData = $this->getBackOrderItemData($orderData['increment_id']);
    if(count($addData) > 1) {
        $addData['status'] = 'Created';
        $addData['back_order_id'] = $orderId;
        $this->setBackorderData($addData);
    }
}
             $checkoutsession->clearQuote();
        }else{
            $result=['error'=>1,'msg'=>'Error there'];
        }

        }catch(Exception $e) {
    } 
        return $result;
    }


}