<?php
namespace Shipsy\EcommerceExtension\Block;

class VirtualSeries extends \Magento\Framework\View\Element\Template
{
    protected $_formKey;
    protected $_cookieManager;
    protected $urlInterface;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlInterface,
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_formKey = $formKey;
        $this->_cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->urlInterface = $urlInterface;
        $this->dataHelper = $dataHelper;
    }

    public function getVirtualSeries()
    {
        $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $base_url = $this->dataHelper->getBaseUrl($this->scopeConfig, $organisation_id);
        
        $headers = [
            'Content-Type:application/json',
            'organisation-id:'.$organisation_id,
            'shop-origin:magento',
            'shop-url:'.$this->urlInterface->getBaseUrl(),
            'customer-id:'.$this->_cookieManager->getCookie('customer-id'),
            'access-token:'.$this->_cookieManager->getCookie('access-token-shipsy')
        ];

        $ch = curl_init($base_url . '/api/ecommerce/getSeries');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        $resultdata = json_decode($result, true);
        return $resultdata;
    }

    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
    
    public function getAuthDetailsFromCookies()
    {
        $authDetails = array(
            "access-token" => $this->_cookieManager->getCookie('access-token-shipsy'), 
            "customer-id" => $this->_cookieManager->getCookie('customer-id'));
        return $authDetails;
    }
}
