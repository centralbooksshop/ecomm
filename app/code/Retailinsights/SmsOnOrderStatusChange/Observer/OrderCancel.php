<?php

namespace Retailinsights\SmsOnOrderStatusChange\Observer;

use Magento\Framework\Event\ObserverInterface;

use \Magento\Framework\Event\Observer       as Observer;
use \Magento\Framework\View\Element\Context as Context;
/**
 * Customer login observer
 */
class OrderCancel implements ObserverInterface
{
       /**
     * Https request
     *
     * @var \Zend\Http\Request
     */
    protected $_request;

    /**
     * Layout Interface
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    public function __construct(
        Context $context,
        \Retailinsights\SmsOnOrderStatusChange\Helper\Data $helperData
       
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_request = $context->getRequest();
        $this->_layout  = $context->getLayout();
        $this->helperData = $helperData;

    }

    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $base_url = $storeManager->getStore()->getBaseUrl();

        
		if (isset($_SERVER['REQUEST_URI'])) {

		if (strpos($_SERVER['REQUEST_URI'], 'order/cancel') !== false) 
        {    
            $orderInfo = $observer->getOrder();
            $incrementId = $orderInfo->getIncrementId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            $custmerName = $order->getShippingAddress()->getData('firstname');

            $msg = "Dear ".$custmerName.", Status of your order ".$incrementId."  has changed to cancelled . Have a nice day, Central Books Online. https://www.CentralBooksOnline.com/home";

            $mobile = $order->getShippingAddress()->getTelephone();
            $sms = $this->helperData->SendSms($msg,"Y",$mobile);
            if($sms==''){
                echo "sms sent successfully";
            }else{
                echo "sms service error";
            }  
        }
        if (strpos($_SERVER['REQUEST_URI'], 'order/hold') !== false) 
        {    
            $orderInfo = $observer->getOrder();
            $incrementId = $orderInfo->getIncrementId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);

            $custmerName = $order->getShippingAddress()->getData('firstname');
            $msg = "Dear ".$custmerName.", Your order #".$incrementId." is on hold ".$base_url;
            $mobile = $order->getShippingAddress()->getTelephone();
            $sms = $this->helperData->SendSms($msg,"Y",$mobile);
            if($sms==''){
                echo "sms sent successfully";
            }else{
                echo "sms service error";
            }  
        }
        if (strpos($_SERVER['REQUEST_URI'], 'order/unhold') !== false) 
        {    
            $orderInfo = $observer->getOrder();
            $incrementId = $orderInfo->getIncrementId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);

            $custmerName = $order->getShippingAddress()->getData('firstname');
            $msg = "Dear ".$custmerName.", Your order #".$incrementId." is unhold and processing ".$base_url;
            $mobile = $order->getShippingAddress()->getTelephone();
            $sms = $this->helperData->SendSms($msg,"Y",$mobile);
            if($sms==''){
                echo "sms sent successfully";
            }else{
                echo "sms service error";
            }  
         }
	  }
    }
}
?>