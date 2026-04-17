<?php

namespace Infomodus\Fedexlabel\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Fedexmethod
 * @package Infomodus\Fedexlabel\Model\Config
 */
class Fedexmethod implements OptionSourceInterface
{
    protected $config;
    protected $storeManager;
    protected $defaultAddress;

    public function __construct(
        \Infomodus\Fedexlabel\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Infomodus\Fedexlabel\Model\Config\Defaultaddress $defaultAddress
    )
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->defaultAddress = $defaultAddress;
    }

    public function toOptionArray($isInternational = false, $countryCode = null)
    {
        $storeId = 0;

        if ($this->config->getRequest()->getParam('website', null) !== null) {
            $storeId = $this->storeManager->getWebsite()->getDefaultStore()->getId();
        } else if ($this->config->getRequest()->getParam('store', null) !== null) {
            $storeId = $this->config->getRequest()->getParam('store');
        }

        if ($countryCode === null) {
            $address = $this->defaultAddress->getAddressesById($this->config->getStoreConfig('fedexlabel/shipping/defaultshipper', $storeId));
            if ($address) {
                $countryCode = $address->getCountry();
            }
        }

        $defaultMethods = [
            ['label' => __('Europe First Priority'), 'value' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY'],
            ['label' => __('1 Day Freight'), 'value' => 'FEDEX_1_DAY_FREIGHT'],
            ['label' => __('2 Day Freight'), 'value' => 'FEDEX_2_DAY_FREIGHT'],
            ['label' => __('2 Day'), 'value' => 'FEDEX_2_DAY'],
            ['label' => __('2 Day AM'), 'value' => 'FEDEX_2_DAY_AM'],
            ['label' => __('3 Day Freight'), 'value' => 'FEDEX_3_DAY_FREIGHT'],
            ['label' => __('Express Saver'), 'value' => 'FEDEX_EXPRESS_SAVER'],
            ['label' => __('Ground'), 'value' => 'FEDEX_GROUND'],
            ['label' => __('First Overnight'), 'value' => 'FIRST_OVERNIGHT'],
            ['label' => __('Home Delivery'), 'value' => 'GROUND_HOME_DELIVERY'],
            ['label' => __('International Economy'), 'value' => 'INTERNATIONAL_ECONOMY'],
            ['label' => __('Intl Economy Freight'), 'value' => 'INTERNATIONAL_ECONOMY_FREIGHT'],
            ['label' => __('International First'), 'value' => 'INTERNATIONAL_FIRST'],
            ['label' => __('International Ground'), 'value' => 'INTERNATIONAL_GROUND'],
            ['label' => __('International Priority'), 'value' => 'INTERNATIONAL_PRIORITY'],
            ['label' => __('Intl Priority Freight'), 'value' => 'INTERNATIONAL_PRIORITY_FREIGHT'],
            ['label' => __('Priority Overnight'), 'value' => 'PRIORITY_OVERNIGHT'],
            ['label' => __('Smart Post'), 'value' => 'SMART_POST'],
            ['label' => __('Standard Overnight'), 'value' => 'STANDARD_OVERNIGHT'],
            ['label' => __('Freight'), 'value' => 'FEDEX_FREIGHT'],
            ['label' => __('National Freight'), 'value' => 'FEDEX_NATIONAL_FREIGHT'],
        ];

        $UKMethods = [
            /* for intra UK only*/
            ['label' => __('Distance Deferred (for intra-UK only)'), 'value' => 'FEDEX_DISTANCE_DEFERRED'],
            ['label' => __('Next Day Afternoon (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_AFTERNOON'],
            ['label' => __('Next Day Early Morning (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_EARLY_MORNING'],
            ['label' => __('Next Day End of Day (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_END_OF_DAY'],
            ['label' => __('Next Day Freight (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_FREIGHT'],
            ['label' => __('Next Day Mid Morning (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_MID_MORNING'],
        ];

        if ($isInternational === true) {
            $c = $defaultMethods;
        } else if ($countryCode && $countryCode == "GB" && $isInternational === false) {
            $c = $UKMethods;
        } else {
            $c = $this->config->arraySum($defaultMethods, $UKMethods);
        }

        return $c;
    }

    public function getFedexMethods()
    {
        $c = [];
        $arr = $this->toOptionArray();
        foreach ($arr as $val) {
            $c[$val['value']] = $val['label'];
        }
        return $c;
    }

    public function getFedexMethodName($code = '')
    {
        $c = [];
        $arr = $this->toOptionArray();
        foreach ($arr as $key => $val) {
            $c[$val['value']] = $val['label'];
        }
        if (array_key_exists($code, $c)) {
            return $c[$code];
        } else {
            return false;
        }
    }

    public function getMethodsByRequest($data, $type = 'global')
    {
        $methods = [];
        foreach ($this->getFedexMethods() as $k => $method) {
            $methods[$k] = $method . (isset($data[$type]) && in_array($k, $data[$type]) ? __(' (Recommended)') : '');
        }
        return $methods;
    }
}
