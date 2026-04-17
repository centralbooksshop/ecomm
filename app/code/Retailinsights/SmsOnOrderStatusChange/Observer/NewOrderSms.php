<?php

namespace Retailinsights\SmsOnOrderStatusChange\Observer;

use Magento\Framework\Event\ObserverInterface;

use \Magento\Framework\Event\Observer       as Observer;
use \Magento\Framework\View\Element\Context as Context;

/*
 * Customer login observer
 */
class NewOrderSms implements ObserverInterface
{
       /**
     * Https request
     *
     * @var \Zend\Http\Request
     */
    protected $_request;
    protected $variable;
    private $logger;

    /**
     * Layout Interface
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    protected $pincodeCollection;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Lof\PincodeChecker\Model\ResourceModel\Pincodechecker\CollectionFactory $pincodeCollection,
        \Retailinsights\SmsOnOrderStatusChange\Helper\Data $helperData,
         \Magento\Sales\Api\Data\OrderInterface $order,
		\Magento\Variable\Model\Variable $variable,
		\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolsCollection,
		\Psr\Log\LoggerInterface $logger
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_request = $context->getRequest();
        $this->_layout  = $context->getLayout();
        $this->order = $order;
		$this->variable = $variable;
		$this->pincodeCollection = $pincodeCollection;
        $this->helperData = $helperData;
        $this->schoolsCollection = $schoolsCollection;
	$this->_storeManager = $storeManager;
	$this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
	    $this->logger->info("Justin sms file entered");
	
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $base_url = $storeManager->getStore()->getBaseUrl();

        $orderId = $observer->getEvent()->getOrderIds();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $incrementId ='';
        $custmerName ='';
		//$accountlink = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getUrl('customer/account');

		$accountlinkvalue = $this->variable->loadByCode('sms-accountlink', 'admin');
		$accountlink = $accountlinkvalue->getPlainValue();

		$helpdesklinkvalue = $this->variable->loadByCode('sms-helpdesklinkvalue', 'admin');
		$helpdesklink = $helpdesklinkvalue->getPlainValue();
		if(empty($accountlink)) {
          $accountlink ='';
		}
		if(empty($helpdesklink)) {
          $helpdesklink ='';
		}

		$sms='Test';
        $prebook = array();
        $incr_array=array();
	if(count($orderId) > 1 && $this->getWebsiteCode() == 'schools') {
		$this->logger->info("Justin spilt order Case". print_r($orderId,true));
            
            //setting student name to Orders*******************  
            $finalGrandTotalprice = 0;
            foreach ($orderId as $key => $value) {
		    $order = $objectManager->create('Magento\Sales\Model\Order')->load($value);
		    $parentOrderIncrementId = $order->getParentSplitOrder();
		    $this->logger->info("parentOrderIncrementId : ". $parentOrderIncrementId);
		    $parentOrderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($parentOrderIncrementId);
		    $parentOrderId = $parentOrderInfo->getId();
		    $this->logger->info("parentOrderId : ". $parentOrderId);
		    $parentOrder = $objectManager->create('\Magento\Sales\Model\Order')->load($parentOrderId);
		    $quote_id = $parentOrder->getQuoteId();
		    $productPurchased = $order->getProductPurchased();
		    $this->logger->info(" quote and product name : ". $quote_id." ".$productPurchased);
		    $quote = $objectManager->create('Magento\Quote\Model\Quote')->load($quote_id);
		    foreach ($quote->getAllItems() as $quoteItem) {
			    $this->logger->info("quote item data : ". $quoteItem->getData('name')." ".$quoteItem->getProduct()->getTypeId()." ".$quoteItem->getData('student_name')." ".$quoteItem->getData('roll_no'));

			    if($quoteItem->getProduct()->getTypeId() == "bundle" && $quoteItem->getData('name') == $productPurchased){
				   $studentName = $quoteItem->getData('student_name');
			           $rollNo = $quoteItem->getData('roll_no');
				   $this->logger->info("Stundent name and roll no ". $studentName." ".$rollNo);
				   $order->setStudentName($studentName);
				   $order->setRollNo( $rollNo);
				   $order->save();
			    }

		    }


	/*	if (isset($_SESSION["student_names"])){
                    foreach ($_SESSION["student_names"] as $i => $val) {
                        if($i == $key){
                            $order->setStudentName($val);
                            $order->save();
                        }
                    }
                }

                if (isset($_SESSION["student_roll"])){
                    foreach ($_SESSION["student_roll"] as $i => $val) {
                        if($i == $key){
                            $order->setRollNo($val);
                            $order->save();
                        }
                    }
		} */

                $incr_array[] = '#'.$order->getIncrementId();
				$finalGrandTotalprice+= $order->getGrandTotal();
				//$finalGrandTotalprice+= $order->getData('subtotal_incl_tax');
				
				 $orderItems = $order->getAllVisibleItems();
                foreach ($orderItems as $item) {
                    $productId = $item->getProductId();
                }
                if($this->isPrebookingEnabled($productId) == '1'){
                    $prebook[] = $productId;
                }
            }

            if(count($prebook) < 1) {
                $incrementId = implode(',', $incr_array);
                $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId[0]);
				
			$shipping_amount = $order->getData('shipping_amount');
		    $GrandTotalprice = $finalGrandTotalprice;
			//$GrandTotalprice = $finalGrandTotalprice + $shipping_amount ;
            $priceHelper = $objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface'); 
			$orderGrandTotal = 'Rs. '. $priceHelper->round($GrandTotalprice); 
            $custmerName = $order->getShippingAddress()->getData('firstname');

			$postcode = '';
			$deliverytime = '3-5';
            $postcode = $order->getShippingAddress()->getPostcode();
			$pincodecollection = $this->pincodeCollection->create();
            $pincodecollection->addFieldToFilter('pincode', array('eq' => $postcode));
            if($pincodecollection->getData()){  
             $pincode_outstation =  $pincodecollection->getFirstItem()->getData('pincode_outstation');
			 $deliverytime = '';
			     if($pincode_outstation == '1'){
				 $deliverytime = '7-10';
				 } 
			 }


                if($order->getStatus() == 'pending'){
  $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                }
                if($order->getStatus() != 'pending'){
                    $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                } 
                $mobile = $order->getShippingAddress()->getTelephone();
                $sms = $this->helperData->SendSms($msg,"Y",$mobile);
            } else {
                foreach ($orderId as $value) {
                   $order = $objectManager->create('Magento\Sales\Model\Order')->load($value);
                    $incrementId =$order->getIncrementId();
            $GrandTotalprice =$order->getGrandTotal();
            $priceHelper = $objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface'); 
			//$orderGrandTotal = $priceHelper->convertAndFormat($GrandTotalprice); 
			//$currency = $priceHelper->getCurrencySymbol();
			$orderGrandTotal = 'Rs. '. $priceHelper->round($GrandTotalprice); 
            $custmerName = $order->getShippingAddress()->getData('firstname');

			$postcode = '';
			$deliverytime = '3-5';
            $postcode = $order->getShippingAddress()->getPostcode();
            $pincodecollection = $this->pincodeCollection->create();
            $pincodecollection->addFieldToFilter('pincode', array('eq' => $postcode));
            if($pincodecollection->getData()){  
             $pincode_outstation =  $pincodecollection->getFirstItem()->getData('pincode_outstation');
			 if($pincode_outstation == '1'){
				 $deliverytime = '7-10';
				 }
			 }

                   if($this->getWebsiteCode() == 'schools'){
                        $orderItems = $order->getAllItems();

                        foreach ($orderItems as $item) {
                            $product_Id = $item->getProductId();
							$product = $objectManager->get('Magento\Catalog\Model\Product')->load($product_Id);
							
							if($product->getTypeId()==='bundle') {
							   $productId = $product->getId();
							}
                        }
                        if($this->isPrebookingEnabled($productId) == '1'){
                            //$msg = $this->getPrebookingMessage($productId);
			$msg = "Dear ".$custmerName.", Your pre booking order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service.  Dispatch date will be informed soon. You can track further status from ".$accountlink.". Reach us at ".$helpdesklink. "for any assistance. - centralbooksonline.com.";

                        }else{
                            if($order->getStatus() == 'pending'){
                                $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                            }
                            if($order->getStatus() != 'pending'){
                                $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                            } 
                        }
                    }else{
                        if($order->getStatus() == 'pending'){
                           $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                        }
                        if($order->getStatus() != 'pending'){
                           $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                        }    
                    }
                    $mobile = $order->getShippingAddress()->getTelephone();
                    $sms = $this->helperData->SendSms($msg,"Y",$mobile);
                }
            }

        } else {
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId[0]);
	    $incrementId =$order->getIncrementId();
	    $this->logger->info("incrementId : ".$incrementId);

            //echo "<pre>";
            //print_r ($order->getData());die;
			$GrandTotalprice =$order->getGrandTotal();

			$priceHelper = $objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface'); 
			//$orderGrandTotal = $priceHelper->convertAndFormat($GrandTotalprice); 
			//$currency = $priceHelper->getCurrencySymbol();
			$orderGrandTotal = 'Rs. '. $priceHelper->round($GrandTotalprice); 
			
			
            $custmerName = $order->getShippingAddress()->getData('firstname');
			$postcode = '';
			$deliverytime = '3-5';
            $postcode = $order->getShippingAddress()->getPostcode();

			$pincodecollection = $this->pincodeCollection->create();
            $pincodecollection->addFieldToFilter('pincode', array('eq' => $postcode));
            if($pincodecollection->getData()){  
             $pincode_outstation =  $pincodecollection->getFirstItem()->getData('pincode_outstation');
			 if($pincode_outstation == '1'){
				 $deliverytime = '7-10';
				 }
			 }

			if($this->getWebsiteCode() == 'schools'){
                $orderItems = $order->getAllItems();

                foreach ($orderItems as $item) {

				$product_Id = $item->getProductId();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($product_Id);
				
					if($product->getTypeId()==='bundle') {
					   $productId = $product->getId();
					}
				}
                if($this->isPrebookingEnabled($productId) == '1'){
                    //$msg = $this->getPrebookingMessage($productId);

                  $msg = "Dear ".$custmerName.", Your pre booking order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service.  Dispatch date will be informed soon. You can track further status from ".$accountlink.". Reach us at ".$helpdesklink. "for any assistance. - centralbooksonline.com.";

                }else{
                    if($order->getStatus() == 'pending'){
                        $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                    }
                    if($order->getStatus() != 'pending'){

                        $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                    } 
                }
            }else{
                if($order->getStatus() == 'pending'){
                   $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                }
                if($order->getStatus() != 'pending'){
                   $msg = "Dear ".$custmerName.",
Your order ".$incrementId." was created successfully for the amount ".$orderGrandTotal.". We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance. - centralbooksonline.com.";
                }    
            }
            $mobile = $order->getShippingAddress()->getTelephone();
            $sms = $this->helperData->SendSms($msg,"Y",$mobile);
        }
        if($sms==''){
            echo "sms sent successfully";
        }else{
            echo "sms service error";
        }  
        unset($_SESSION["student_names"]);
    }

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }

    public function getPrebookingMessage($newProductId)
    {
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $products = $productFactory->create()                              
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id',['eq'=>$newProductId])
                    ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched     
        }catch(\Exception $e){
            $this->logger->error($e->getMessage());
        }
        $school_name = $products->getFirstItem()->getData('school_name');

        $collection = $this->schoolsCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('school_name', $school_name);
        return $collection->getFirstItem()->getData('prebooking_description');

    }

    public function isPrebookingEnabled($productId)
    {
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $products = $productFactory->create()                              
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id',['eq'=>$productId])
                    ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched     
        }catch(\Exception $e){
            $this->logger->error($e->getMessage());
        }
        $school_name = $products->getFirstItem()->getData('school_name');

        $collection = $this->schoolsCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('school_name', $school_name);
        return $collection->getFirstItem()->getData('enable_prebooking');
    }
}

