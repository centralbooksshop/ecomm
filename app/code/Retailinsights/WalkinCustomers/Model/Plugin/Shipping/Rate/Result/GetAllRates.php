<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Retailinsights\WalkinCustomers\Model\Plugin\Shipping\Rate\Result;

class GetAllRates
{
    /**
     * @param $subject
     * @param $result
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method[]
     */
    public function afterGetAllRates($subject, $result)
    {
        $availableMethods = [
            'freeshipping_freeshipping'
        ];

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method[] $result */
        /**
         * @var int $key
         * @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
         */
        
        foreach ($result as $key => $rate) {
            $code = $rate->getCarrier() . '_' . $rate->getMethod();
            if ((!in_array($code, $availableMethods)) && ($this->checkCustomerGroup())) {
                unset($result[$key]);
            }
            if ((in_array($code, $availableMethods)) && (!$this->checkCustomerGroup())) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    public function checkCustomerGroup(){
        $om = \Magento\Framework\App\ObjectManager::getInstance();  
        $customerSession = $om->get('Magento\Customer\Model\Session');  

        if($customerSession->getCustomer()->getGroupId() == '2'){
            return true;
        }
        return false;
    }

    public function getWebsiteId()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();  
        $_storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');  
        return $_storeManager->getStore()->getWebsiteId();
    }

}
?>