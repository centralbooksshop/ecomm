<?php
namespace Shipsy\EcommerceExtension\Block;

class Setup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    protected $_item;
    protected $_renderer;
    protected $urlInterface;
    protected $formKey;
    protected $_countryFactory;
    protected $_cookieManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Model\ProductRepository $item,
        \Magento\Sales\Model\Order\Address\Renderer $renderer,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->_renderer = $renderer;
        $this->_item = $item;
        $this->urlInterface = $urlInterface;
        $this->formKey = $formKey;
        $this->_countryFactory = $countryFactory;
        $this->_cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
    }
    
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getAddresses()
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

        $ch = curl_init($base_url . '/api/ecommerce/getshopdata');

        curl_setopt($ch, CURLOPT_POST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, []);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        $resultdata = json_decode($result, true);
        return $resultdata;
    }

    public function getCountryName($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    public function getBaseUrl()
    {
        return $this->_urlInterface->getBaseUrl();
    }

    public function getAuthDetailsFromCookies()
    {
        $authDetails = array(
            "access-token" => $this->_cookieManager->getCookie('access-token-shipsy'), 
            "customer-id" => $this->_cookieManager->getCookie('customer-id'));
        return $authDetails;
    }

    public function getW3wConfig()
    {
        return $this->scopeConfig->getValue('configuration/services/enable_w3w_input', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
