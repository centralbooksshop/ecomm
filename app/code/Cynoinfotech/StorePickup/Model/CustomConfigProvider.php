<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
 
namespace Cynoinfotech\StorePickup\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class CustomConfigProvider implements ConfigProviderInterface
{
    protected $storeManager;
    protected $scopeConfig;
    protected $checkoutSession;
    protected $schoolData;
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Cynoinfotech\StorePickup\Helper\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        \SchoolZone\Addschool\Model\SimilarproductsattributesFactory $schoolData
    ) {
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->storepickup = $storepickupFactory;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->schoolData = $schoolData;
    }

    public function getConfig()
    {
        $storepick_config = [];
        $storepick_info = [];
        $storepick_location = [];
        $apikey = $this->dataHelper->getApiKey();
        $storePickupTimings = $this->getSchoolBasedStorepickupTimings($this->dataHelper->getPickupHoursStart(), $this->dataHelper->getPickupHoursEnd());
        $hour_min = $storePickupTimings['hour_min'];
        $hour_max = $storePickupTimings['hour_max'];
        $zoom_level = $this->dataHelper->getMapZoomLevel();
        
        $store_lat = $this->dataHelper->getStoreLat();
	$store_lng = $this->dataHelper->getStoreLng();
	$storepickup = $this->storepickup->create();
	$schoolPickupStoreId = $this->getSchoolBasedPickupStore();
	if($schoolPickupStoreId != ''){
   	   $collection = $storepickup->getCollection()->addFieldToFilter('entity_id', $schoolPickupStoreId);	
	}else{
	   $collection = $storepickup->getCollection();
	}

        foreach ($collection as $item) {
            $storepick_config[] = [
                                    'value' => $item->getData('entity_id'),
                                    'label' => $item->getData('name'). ' - '. $item->getData('store_address').', '.$item->getData('store_city').', '.$item->getData('store_country')
                                   ];
            
            $storepick_info[] = ['value' => 'store_info_'.$item->getData('entity_id'),
                'label' => $item->getData('name').', '.
                $item->getData('store_address').', '.
                $item->getData('store_city').', '.
                $item->getData('store_state').',-'.
                $item->getData('store_pincode').', '.
                $item->getData('store_country').',  T:'.
                $item->getData('store_phone').', email: '.
                $item->getData('store_email')];
                
            $storepick_location[] =[$item->getData('name'),
                $item->getData('store_latitude'),
                $item->getData('store_longitude')
            ];
        }
        
        $config = [
            'storepick_config' => $storepick_config,
            'storepick_info' => $storepick_info,
            'storepick_location' => $storepick_location,
            'apikey' => $apikey,
            'hour_min' => $hour_min,
            'hour_max' => $hour_max,
            'zoom_level' => $zoom_level,
            'store_lat' => $store_lat,
            'store_lng' => $store_lng,
            'storepick_config_encode' => json_encode($storepick_config),
        ];
        return $config;
    }

    private function getSchoolBasedStorepickupTimings($hour_min, $hour_max)
    {
        $schoolId = $this->checkoutSession->getQuote()->getSchoolId();
        if ($schoolId) {
            $data = $this->schoolData->create()->getCollection()->addFieldToFilter('school_name', $schoolId)->getFirstItem();
            if ($data->getEnableStorepickup() && $data->getStorepickupTimings()) {
                $timingsArray = explode('-', $data->getStorepickupTimings());
                $hour_min = $timingsArray[0] ?? $hour_min;
                $hour_max = $timingsArray[1] ?? $hour_max;
            }
        }
        return ['hour_min' => $hour_min.": 00", 'hour_max' => $hour_max.": 00"];
    }

   private function getSchoolBasedPickupStore()
    {
	$schoolId = $this->checkoutSession->getQuote()->getSchoolId();
	$pickupStoreValue = '';
        if ($schoolId) {
            $data = $this->schoolData->create()->getCollection()->addFieldToFilter('school_name', $schoolId)->getFirstItem();
            if ($data->getEnableStorepickup() && $data->getPickupStores()) {
                $pickupStoreValue = $data->getPickupStores();
	    } 
	} 
        return $pickupStoreValue;
    } 
}
