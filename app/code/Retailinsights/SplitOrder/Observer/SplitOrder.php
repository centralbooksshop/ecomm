<?php
 
namespace Retailinsights\SplitOrder\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
 
class SplitOrder implements ObserverInterface
{
    protected $logger;
    protected $productRepository;
    protected $helperData;

  public function __construct(
        \Retailinsights\SplitOrder\Helper\Data $helperData,
        LoggerInterface $logger,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Contact\Model\ConfigInterface $contactConfig)
  {
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->orderModel = $orderModel;
        $this->resource = $resource;
        $this->orderManagement = $orderManagement;
        $this->contactConfig = $contactConfig;
  }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
           $storeId = $this->storeManager->getStore()->getId();
           $storeName= $this->storeManager->getStore()->getName();
           $orderIds = $observer->getEvent()->getOrderIds();
           $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $orderData=array();
           $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderIds[0]);
            $orderItems = $order->getAllVisibleItems();
           $produtcount = count($orderItems);
            $product_ids=array();
              foreach ($orderItems as $item) {
         $productId =  $item->getId();
            $product_ids[] = array($productId => $item->getQtyOrdered());
     }
$status = 'fail';
// if($produtcount > 1) {
//     $this->logger->info('Product ids'.json_encode($product_ids));
//    foreach ($product_ids as $value) {
//     $this->logger->info('Product pass'.json_encode($value));
//           $order_details1 = $this->getAllDetailsOne($main_order_id, $value);
//            $checkoutsession = $objectManager->get('Magento\Checkout\Model\Session');
//            $checkoutsession->clearQuote();
//           $order1 = $this->helperData->createMageOrder($order_details1);
//            if (isset($order1['error'])) {
//                         throw new \Exception($order1['msg']);
//                          $this->logger->info('Order error'.$order1['msg']);
//            } else {
//             $status = 'success';
//              $this->logger->info('Success');
//            }
//            //exit();
//      } 
//      if($status == 'success') {
//          $this->orderManagement->cancel($main_order_id);
//                     // $order->setStatus("order_split");
//              echo "Successfully Created To Ids";       
//      } 
// }
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

        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('sales_order_item');

        $order = $this->orderModel->create()->load($order_id);

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