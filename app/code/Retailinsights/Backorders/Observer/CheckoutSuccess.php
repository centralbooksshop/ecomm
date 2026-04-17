<?php
 
namespace Retailinsights\Backorders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
 
class CheckoutSuccess implements ObserverInterface
{
    protected $logger;
    protected $productRepository;
    protected $helperData;
    protected $_productCollectionFactory;
    protected $stockRegistry;
 
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
         \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Retailinsights\Backorders\Helper\Data $helperData)
    {
        $this->stockRegistry = $stockRegistry;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
           $orderIds = $observer->getEvent()->getOrderIds();
           $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $orderData=array();
           foreach ($orderIds as $order_id) {
           $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
            $orderItems = $order->getAllItems();
     $order_status ='';
     $back_order_data = array();
     $product_skus = array();
     $product_qtys = array();
    foreach ($orderItems as $item) {
         $productId =  $item->getProductId();
		  if($this->helperData->isBackordred($item)){
          $order_status = 'Yes';
          $product_skus[] = $item->getSku();
          $product_qtys[] = $item->getQtyOrdered();
          // $this->logger->info($productCollection->getData()); 
            $this->logger->info($item->getSku()); 

             $obj = \Magento\Framework\App\ObjectManager::getInstance();  
             $getSalable = $obj->get('Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
             $salable = $getSalable->execute($item->getSku());

            // $this->logger->info($item->getQtyOrdered()); 
            //$this->logger->info("befor");
            //$this->logger->info($salable);

            foreach ($salable as $key => $value) {
                $salable = $value['qty']; 
             } 
            
            $qty = $item->getQtyOrdered(); 
            $sku = $item->getSku();
            $salableqty = $qty+$salable;

            $this->logger->info('sku:'.$sku);
            $this->logger->info('qty:'.$qty);
            $this->logger->info('salable:'.$salable);
            $this->logger->info('salableqty:'.$salableqty);
           

            // $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            // $stockItem->setQty($salableqty);

            // $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

            // $salable = $getSalable->execute($item->getSku());
            // $this->logger->info("after"); 
            // $this->logger->info($salable); 

         }
     }
     if($order_status == 'Yes') {


       $back_order_data['order_id'] = $order->getIncrementId();
       $back_order_data['item_id']  = $order->getEntityId();
       $back_order_data['sku']  = implode(",", $product_skus);
       $back_order_data['qty_ordered']  = implode(",", $product_qtys);
       $back_order_data['status']  = 'New';
       $this->helperData->setBackorderData($back_order_data);
      $order->setIsBackeorderedItems($order_status); 
     }
      
      $orderIncrementId = $order->getIncrementId();
            $order_check = explode("-",$orderIncrementId);
        if(count($order_check) > 1){
    $this->logger->info('Its Backorder - '.$orderIncrementId);
    $order->setShippingAmount(0);
   $order->setBaseShippingAmount(0);
   $order->setGrandTotal(0);
   $order->setBaseGrandTotal(0);
        }
            $order->save();
//             $orderItems = $order->getAllItems();
//             $product_ids=array();
//               foreach ($orderItems as $item) {
//          $productId =  $item->getProductId();
//          if($this->helperData->isBackordred($item)){
//             $product_ids[] = array('product_id'=>$productId,"qty"=>$item->getQtyOrdered());
//          } 
//      }
//      if(count($product_ids) > 0) {
//          $this->logger->info(json_encode($product_ids));
//      $firstname= $order->getShippingAddress()->getFirstname();
//      $lastname= $order->getShippingAddress()->getLastname();
//      $street = $order->getShippingAddress()->getStreet();
//      $city = $order->getShippingAddress()->getCity();
//      $region = $order->getShippingAddress()->getRegion();
//      $postcode = $order->getShippingAddress()->getPostcode();
//      $telephone = $order->getShippingAddress()->getTelephone();
//          $addressData=[
// 'firstname' => $firstname, //address Details
// 'lastname' => $lastname,
// 'street' => $street,
// 'city' => $city,
// 'country_id' => $order->getShippingAddress()->getCountryId(),
// 'region' => $region, // replace with region
// 'postcode' => $postcode, // replace with real zip code
// 'telephone' => $telephone,
// 'save_in_address_book' => 0 // If you want to save in address book
// ];
//      $shippingAddress= $order->getShippingAddress();
//      $customer_email= $order->getCustomerEmail();
//       $store_id = $order->getStoreId();
//      $orderData = array('currency_id'=>'INR','store_id'=>$store_id,'payment_method'=>$order->getPayment()->getMethod(),'increment_id'=>$order->getIncrementId(),'shipping_method'=>$order->getShippingMethod(),'email'=>$customer_email,'shipping_address'=>$addressData,'items'=>$product_ids);
//         $this->logger->info(json_encode($orderData));
       
//         $orderIncrementId = $order->getIncrementId();
//         $order_check = explode("-",$orderIncrementId);
//         if(count($order_check) <= 1){
//          $order_result = $this->helperData->createBackOrder($orderData);
//         $this->logger->info(json_encode($order_result));
//         }
        
//      }

           }

        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}