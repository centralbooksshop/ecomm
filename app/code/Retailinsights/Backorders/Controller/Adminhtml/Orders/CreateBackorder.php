<?php
declare(strict_types=1);

/**
 * @author tjitse (Vendic)
 * Created on 16/01/2019 18:13
 */

namespace Retailinsights\Backorders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

class CreateBackorder extends Action
{
    protected $resultRedirectFactory = false;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Settings
     */
    protected $settings;

    protected $helperData;

       /**
     * Array of actions which can be processed without secret key validation
     *
     * @var string[]
     */
    protected $_publicActions = ['index','createbackorder'];
	protected $logger;

    public function __construct(
        Action\Context $context,
        \Retailinsights\Backorders\Helper\Data $helperData,
		\Psr\Log\LoggerInterface $logger,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
		$this->logger = $logger;

    }

    /**
     * Loads layout file vendic_extraordergrid_orders_index and sets title from settings.
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {

         try {
           $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $orderData=array();
           $order_id = $this->getRequest()->getParam('order_id');;
           $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
           $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
           $storeManager->getStore($order->getStoreId())->getStoreId();

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
     $region_id = $order->getShippingAddress()->getRegionId();
     $postcode = $order->getShippingAddress()->getPostcode();
     $telephone = $order->getShippingAddress()->getTelephone();
         $addressData=[
'firstname' => $firstname, //address Details
'lastname' => $lastname,
'street' => $street,
'city' => $city,
'country_id' => $order->getShippingAddress()->getCountryId(),
'region' => $region, // replace with region
'region_id' =>$region_id,
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

          $orderIncrementId = $order->getIncrementId();
        $order_check = explode("-",$orderIncrementId);
        if(count($order_check) <= 1){
        $order_result = $this->helperData->createBackOrder($orderData);
       if(isset($order_result['order_id'])) {
           $this->messageManager->addSuccess(__('Backorder Created Successfully'));
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order_result['order_id']
                ]
            );
       } else {
        $this->messageManager->addError(__('Some Error Happen Try Again '));
       }
        } else {
             $this->messageManager->addError(__('Some Error Happen Try Again '));
        }
     
     }

        } catch (\Exception $e) {
           $this->logger->info($e->getMessage());
        }

    return $this->resultRedirectFactory->create()->setPath('sales/*/');
        
    }

}
