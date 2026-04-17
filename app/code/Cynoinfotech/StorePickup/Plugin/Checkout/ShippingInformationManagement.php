<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Plugin\Checkout;

class ShippingInformationManagement
{
    protected $storepickupHelper;

    public function __construct(
        \Cynoinfotech\StorePickup\Helper\Data $storepickupHelper
    ) {
        $this->storepickupHelper = $storepickupHelper;
    }

    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();

		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/aroundsaveaddressinformation.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('extAttributes Array Log'.print_r($extAttributes, true));
        $logger->info("getStorepickupshippingchecked ".$extAttributes->getStorepickupShippingChecked());
 

        if ($extAttributes instanceof \Magento\Checkout\Api\Data\ShippingInformationExtension) {
            $data = [];
            if ($extAttributes->getStorepickupShippingChecked()) {
                $data = [
                    'store_pickup'         => $extAttributes->getStorePickup(),
                    'calendar_inputField'  => $extAttributes->getCalendarInputField(),
                    'pickup_person_name'  => $extAttributes->getPickupPersonName(),
                    'pickup_person_id'     => $extAttributes->getPickupPersonId()
                ];
            }
            $this->storepickupHelper->setStorepickupDataToSession($data);
        }
        
        return $proceed($cartId, $addressInformation);
    }
}
