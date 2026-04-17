<?php

namespace Retailinsights\SmsOnOrderStatusChange\Observer;

use Magento\Framework\Event\ObserverInterface;

use \Magento\Framework\Event\Observer       as Observer;
use \Magento\Framework\View\Element\Context as Context;
/**
 * Customer login observer
 */
class ShipmentSms implements ObserverInterface
{
       /**
     * Https request
     *
     * @var \Zend\Http\Request
     */
    protected $_request;
	protected $variable;

    /**
     * Layout Interface
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    protected $storeManager;

    public function __construct(
        Context $context,
         \Retailinsights\SmsOnOrderStatusChange\Helper\Data $helperData,
         \Magento\Sales\Api\Data\OrderInterface $order,
		 \Magento\Variable\Model\Variable $variable,
         \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_request = $context->getRequest();
        $this->_layout  = $context->getLayout();
        $this->order = $order;
		$this->variable = $variable;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;

    }

    public function execute(Observer $observer)
    {
       
		$shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
		$tracksCollection = $shipment->getTracksCollection();
		foreach ($tracksCollection->getItems() as $track) {
		   $trackNumber = $track->getTrackNumber();
		   $carrierName = $track->getTitle();
		   $carriercode = $track->getCarrierCode();
		}
       
         
       //echo "<pre>"; print_r($shipment->debug()); 
        $orderId = $order->getIncrementId();
        $base_url = $this->storeManager->getStore()->getBaseUrl();
        $custmerName = $order->getShippingAddress()->getData('firstname');
		$helpdesklinkvalue = $this->variable->loadByCode('sms-helpdesklinkvalue', 'admin');
		$helpdesklink = $helpdesklinkvalue->getPlainValue();
		if(empty($helpdesklink)) {
          $helpdesklink ='';
		}

		$accountlinkvalue = $this->variable->loadByCode('sms-accountlink', 'admin');
		$accountlink = $accountlinkvalue->getPlainValue();
		if(empty($accountlink)) {
          $accountlink ='';
		}

       if(!empty($carriercode)){
		   if($carriercode =='ecomexpress'){ 
		   //$tracklink = 'https://ecomexpress.in/tracking/?awb_field='.$trackNumber;
		   $tracklink = 'https://ecomexpress.in';
	$msg = "Dear ".$custmerName.", Your order ".$orderId." was picked by ".$carrierName." and your tracking number is ".$trackNumber." You can track your order using this link ".$tracklink.". You will receive the order in 5 working days. Reach us at ".$helpdesklink." for any assistance.
- centralbooksonline.com.";
	
		   
		   } elseif ($carriercode =='delhivery') {

			//$tracklink = 'https://www.delhivery.com/track/package/'.$trackNumber;
			$tracklink = 'https://www.delhivery.com';
	      $msg = "Dear ".$custmerName.", Your order ".$orderId." was picked by ".$carrierName." and your tracking number is ".$trackNumber." You can track your order using this link ".$tracklink.". You will receive the order in 5 working days. Reach us at ".$helpdesklink." for any assistance.
- centralbooksonline.com.";
			 } elseif ($carriercode =='dtdc') {

			//$tracklink = 'https://centralbooks.clickpost.ai/?waybill='.$trackNumber;
			$tracklink = 'https://www.dtdc.com/track-your-shipment';
		$msg = "Dear ".$custmerName.", Your order ".$orderId." was picked by ".$carrierName." and your tracking number is ".$trackNumber." You can track your order using this link ".$tracklink.". You will receive the order in 5 working days. Reach us at ".$helpdesklink." for any assistance.
- centralbooksonline.com.";
	    } elseif ($carriercode =='elasticrun') {

			$tracklink = 'https://centralbooksonline.com';
		$msg = "Dear ".$custmerName.", Your order ".$orderId." was picked by ".$carrierName." and your tracking number is ".$trackNumber." You can track your order using this link ".$tracklink.". You will receive the order in 5 working days. Reach us at ".$helpdesklink." for any assistance.
- centralbooksonline.com.";
	      } elseif ($carriercode =='amazon') {

			$tracklink = 'https://centralbooksonline.com';
		$msg = "Dear ".$custmerName.", Your order ".$orderId." was picked by ".$carrierName." and your tracking number is ".$trackNumber." You can track your order using this link ".$tracklink.". You will receive the order in 5 working days. Reach us at ".$helpdesklink." for any assistance.
- centralbooksonline.com.";
	      } elseif ($carriercode =='smcs') {

			$tracklink = 'https://shreemaruti.com/track-shipment';
		$msg = "Dear ".$custmerName.", Your order ".$orderId." was picked by ".$carrierName." and your tracking number is ".$trackNumber." You can track your order using this link ".$tracklink.". You will receive the order in 5 working days. Reach us at ".$helpdesklink." for any assistance.
- centralbooksonline.com.";
		
	   }
		
	   } else {
$msg = "Dear ".$custmerName.", Your order ".$orderId." is processed and we'll update the courier details soon. We ensure the best service, you can track further status from ".$accountlink.". Reach us at ".$helpdesklink." for any assistance.
- centralbooksonline.com.";
	    } 
	   
		$mobile = $order->getShippingAddress()->getTelephone();
        $sms = $this->helperData->SendSmsShipment($msg,"Y",$mobile);
    }
}