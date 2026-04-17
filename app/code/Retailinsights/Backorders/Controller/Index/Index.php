<?php
namespace Retailinsights\Backorders\Controller\Index;


class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $helperData;
	protected $logger;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Retailinsights\Backorders\Helper\Data $helperData,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->helperData = $helperData;
		$this->logger = $logger;
		return parent::__construct($context);
	}

	public function execute()
	{
		  try {
           $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $orderData=array();
           $order_id = 560;
           $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
           $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
          echo  $storeManager->getStore($order->getStoreId())->getStoreId();

            $orderItems = $order->getAllItems();
            $product_ids=array();
              foreach ($orderItems as $item) {
         $productId =  $item->getProductId();
         $this->logger->info('Backorders IDs'.$productId);
         if($this->helperData->isBackordred($item)){
            $product_ids[] = array('product_id'=>$productId,"qty"=>$item->getQtyOrdered());
         } 
     }

     if(count($product_ids) > 0) {
         $this->logger->info('inside Loop of backorder');
         $this->logger->info(json_encode($product_ids));
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

     $customer_email= $order->getCustomerEmail();
     $store_id = $order->getStoreId();
     $orderData = array('currency_id'=>'INR','store_id'=>$store_id,'payment_method'=>$order->getPayment()->getMethod(),'increment_id'=>$order->getIncrementId(),'shipping_method'=>$order->getShippingMethod(),'email'=>$customer_email,'shipping_address'=>$addressData,'items'=>$product_ids);
        $this->logger->info(json_encode($orderData));
        $checkoutsession = $objectManager->get('Magento\Checkout\Model\Session');
        $checkoutsession->clearQuote();
// $res = $this->helperData->udateBackorderData($increment_id, $orderData);
// echo $res;
         $orderIncrementId = $order->getIncrementId();
        $order_check = explode("-",$orderIncrementId);
        if(count($order_check) <= 1){
         echo 'inside offer';
        } else {
            echo 'outside offer';
        }
     $order_result = $this->helperData->createBackOrder($orderData);

      print_r($order_result);
     }

           

        } catch (\Exception $e) {
           $this->logger->info($e->getMessage());
        }
	}
}