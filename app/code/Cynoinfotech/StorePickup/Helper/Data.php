<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

   
     /**
      * @var Storage
      */
    private $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Session\Storage $sessionStorage,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->session = $sessionStorage;
        $this->storeManager = $storeManager;
        $this->storepickup = $storepickupFactory;
        $this->_scopeConfig = $scopeConfigObject;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function getConfig($configPath)
    {
        return $this->_scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getEnableModule()
    {
        return $this->getConfig('carriers/cistorepickup/active');
    }
    
    public function getApiKey()
    {
        return $this->getConfig('carriers/cistorepickup/gmap_api');
    }
    
    public function getPickupHoursStart()
    {
        return $this->getConfig('carriers/cistorepickup/pickuphoursstart');
    }
    
    public function getPickupHoursEnd()
    {
        return $this->getConfig('carriers/cistorepickup/pickuphoursend');
    }
    
    public function getMapZoomLevel()
    {
        return $this->getConfig('carriers/cistorepickup/zoom_level');
    }
    
    public function getStoreLat()
    {
        return trim($this->getConfig('carriers/cistorepickup/store_lat'));
    }
    
    public function getStoreLng()
    {
        return $this->getConfig('carriers/cistorepickup/store_lng');
    }

    /**
     * Save Data to session
     *
     * @param array $data
     */
    public function setStorepickupDataToSession($data)
    {
        $this->session->setData($this->getStorepickupAttributesSessionKey(), $data);
    }

    /**
     * load Data to sassion
     *
     * @return array
     */
    public function getStorepickupDataFromSession()
    {
        return $this->session->getData($this->getStorepickupAttributesSessionKey());
    }

    /**
     * get Session Key
     *
     * @return string
     */
    public function getStorepickupAttributesSessionKey()
    {
        return 'cistorepickup';
    }
    
    public function getLatLang()
    {
        $lat_lang = [];
        $storepickup = $this->storepickup->create();
        $collection = $storepickup->getCollection();
        
        foreach ($collection as $item) {
            $lat_lang[] = [$item->getData('name').','
                    .$item->getData('store_latitude').','
                    .$item->getData('store_longitude')];
        }
        
        return json_encode($lat_lang);
    }
}
