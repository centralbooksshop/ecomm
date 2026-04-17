<?php

namespace Shipsy\EcommerceExtension\Helper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Data extends AbstractHelper
{

    protected $cookieManager;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CookieManagerInterface $cookieManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cookieManager = $cookieManager;
        $this->logger = $logger;
    }

    public function getConfig($scope, $path)
    {
        try {
            switch ($scope) {
                case 'store':
                    return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);        // From store view
                case 'website':
                    return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE);    // From Website
                default:
                    return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
            }
 
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConsignmentDetails($customerReferenceNumberList) {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/dtdc_status.log');
			$logger = new \Zend_Log();
			$logger->addWriter($writer);
			
			//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
            $baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

			//$base_url = $this->scopeConfig->getValue('configuration/services/shipsy_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
            $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$base_url = $this->getBaseUrl($this->scopeConfig, $organisation_id);
            $customerId = $this->scopeConfig->getValue('shipsy_customer_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $accessToken = $this->scopeConfig->getValue('shipsy_access_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $dataToSendJson = json_encode(['customerReferenceNumberList' => $customerReferenceNumberList]);

            $headers = [
                'Content-Type:application/json',
                'organisation-id:'.$organisation_id,
                'shop-origin:magento',
				'shop-url:' . $baseUrl,
                'customer-id:'.$customerId,
                'access-token:'.$accessToken
            ];
			//$logger->info('headers'.print_r($headers, true));

            $ch = curl_init($base_url . '/api/ecommerce/getawbnumber');
            $this->_logger->debug("Logging Headers to debug.log");
            $this->_logger->log(100, json_encode($headers));
            curl_setopt($ch, CURLOPT_POST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSendJson);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            curl_close($ch);
            $responsedata = json_decode($response, true);
            return $responsedata;
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getBaseUrl($scopeConfig, $organisationId)
    {
        $integration_url_hash = array(
            "1" => "dtdc_projectx_url"
        );

        $url_to_find = NULL;
        $integration_url = NULL;
        
        if (array_key_exists($organisationId, $integration_url_hash)) {
            $url_to_find = $integration_url_hash[ $organisationId ];
        }
        
        if (!is_null($url_to_find)) {
            $integration_url = $this->scopeConfig->getValue('configuration/services/' . $url_to_find, ScopeInterface::SCOPE_STORE);
        }

        if (is_null($integration_url)) {
            $integration_url = $this->scopeConfig->getValue('configuration/services/shipsy_url', ScopeInterface::SCOPE_STORE);
        }

        return $integration_url;
    }

    public function getAddresses()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$customerId = $this->scopeConfig->getValue('shipsy_customer_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //$accessToken = $this->scopeConfig->getValue('shipsy_access_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $customerId = $this->cookieManager->getCookie('customer-id');
            $accessToken = $this->cookieManager->getCookie('access-token-shipsy');
            $base_url = $this->scopeConfig->getValue('configuration/services/shipsy_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
            $headers = [
                'Content-Type:application/json',
                'organisation-id:'.$organisation_id,
                'shop-origin:magento',
                'shop-url:'.$storeManager->getStore()->getBaseUrl(),
                'customer-id:'.$customerId,
                'access-token:'.$accessToken
            ];

            $ch = curl_init($base_url . '/api/ecommerce/getshopdata');

            curl_setopt($ch, CURLOPT_POST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, []);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);
            $resultdata = json_decode($result, true);
            return $resultdata;
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
