<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Api\Deliveryboy;

use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Model\Deliveryboy as Deliveryboy;

class OtptoAlternate extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * Alternate OTP to customer.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
		$this->verifyRequest();

            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
	    $this->logger->info('Store ID: ' . $this->storeId);
	    $this->logger->info('alternateMobile : ' . $this->alternateMobile);
	    $this->logger->info('incrementId : ' . $this->incrementId);
	    $this->logger->info('deliveryboyId : ' .$this->deliveryboyId );
	    $this->logger->info('alternateContactName : ' . $this->alternateContactName);
	    $this->logger->info('personRelation: ' . $this->personRelation);
		
	   
            $deliveryboyOrderCollection = $this->deliveryboyOrderResourceCollection->create()
                ->addFieldToFilter("increment_id", $this->incrementId)
                ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
	    $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();
	    $order = $this->orderFactory->create()->load($deliveryboyOrder->getOrderId());
	    $registedPhoneNumber = $order->getShippingAddress()->getTelephone();
	    $firstname = $order->getShippingAddress()->getFirstname();
	    $lastname = $order->getShippingAddress()->getLastname();
	    $registredName = $firstname." ".$lastname;
           // $this->logger->info(' Registed Phone Number : ' .$order->getShippingAddress()->getTelephone());
	    $this->logger->info('Order ID: ' . $deliveryboyOrder->getOrderId());
	   // $this->logger->info('OTP : '.$deliveryboyOrder->getOtp());
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $helper = $objectManager->get('Retailinsights\Registers\Helper\Data');
	   // $helper->SendSms("Use Code", "to login for centralbooksonline.", "Y", $deliveryboyOrder->getOtp(), $this->alternateMobile);
	    
	    if($this->alternateContactName != "" && $this->personRelation != "" && $this->alternateMobile != "" && $registedPhoneNumber != ""){
		$helper->SendSms("Use Code", "to login for centralbooksonline.", "Y", $deliveryboyOrder->getOtp(), $this->alternateMobile);
	   	/*$prefix = "Dear ".$this->alternateContactName.",";
	    	$sufix = "this message is to remind that the Purchase Order ".$this->personRelation;
	    	$endtail = " has pending items to deliver. - Central Books.";
		$helper->SendSms($prefix, $endtail, "Y", $sufix, $registedPhoneNumber); */


		$prefix = "Dear ".$registredName.",";
                $sufix = "Your order ".$this->incrementId." has been successfully delivered to the person named ".$this->alternateContactName." and the relation is ".$this->personRelation.".";
                $endtail = " Reach us at https://centralbooksonline.com/ for any assistance. - centralbooksonline.";
		$helper->SendSms($prefix, $endtail, "Y", $sufix, $registedPhoneNumber);


	    }else{
	   	$this->logger->info(' Registed Phone Number : ' .$order->getShippingAddress()->getTelephone());
 	        $this->logger->info('OTP : '.$deliveryboyOrder->getOtp());	
	    	$helper->SendSms("Use Code", "to login for centralbooksonline.", "Y", $deliveryboyOrder->getOtp(), $registedPhoneNumber);
	    }

	    $this->returnArray["message"] = __("OTP sent Successfully.");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify request.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {
	    

        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? 0);
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
	    $this->alternateMobile = trim($this->wholeData["alternateMobile"] ?? "");    
	    $this->alternateContactName = trim($this->wholeData["alternateContactName"] ?? "");
	    $this->personRelation = trim($this->wholeData["personRelation"] ?? "");

        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
        if ($this->incrementId == "") {
            throw new LocalizedException(__("Invalid Order."));
        }
        if (!($this->deliveryboyId > 0 &&
             $this->deliveryboy->load($this->deliveryboyId)->getId() == $this->deliveryboyId)
        ) {
            throw new LocalizedException(__("Invalid delivery boy."));
        }
        if ($this->alternateMobile == "") {
            throw new LocalizedException(__("Invalid Alternate Mobile number."));
        }
    }
}
