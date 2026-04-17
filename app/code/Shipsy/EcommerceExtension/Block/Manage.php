<?php
namespace Shipsy\EcommerceExtension\Block;

class Manage extends \Magento\Framework\View\Element\Template
{
    protected $orderRepository;
    protected $item;
    protected $renderer;
    protected $urlInterface;
    protected $formKey;
    protected $countryFactory;
    protected $cookieManager;

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
        $this->renderer = $renderer;
        $this->item = $item;
        $this->urlInterface = $urlInterface;
        $this->formKey = $formKey;
        $this->countryFactory = $countryFactory;
        $this->cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
    }
    
    public function getReferenceNumber($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        return $order->getIncrementId();
    }

    public function getAwbNumber()
    {
        $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $base_url = $this->dataHelper->getBaseUrl($this->scopeConfig, $organisation_id);
        $params = $this->getRequest()->getParams();
        $orderIds = explode(',', $params['id']);
        $orderIds = array_map([$this, 'getReferenceNumber'], $orderIds);
        $dataToSendJson = json_encode(['customerReferenceNumberList' => $orderIds]);
        $headers = [
            'Content-Type:application/json',
            'organisation-id:'.$organisation_id,
            'shop-origin:magento',
            'shop-url:'.$this->urlInterface->getBaseUrl(),
            'customer-id:'.$this->cookieManager->getCookie('customer-id'),
            'access-token:'.$this->cookieManager->getCookie('access-token-shipsy')
        ];

        $ch = curl_init($base_url . '/api/ecommerce/getawbnumber');

        curl_setopt($ch, CURLOPT_POST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSendJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        $resultdata = json_decode($result, true);
        return $resultdata;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getBaseUrl()
    {
        return $this->urlInterface->getBaseUrl();
    }

    public function getBaseUrlFromHelper()
    {
        $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->dataHelper->getBaseUrl($this->scopeConfig, $organisation_id);
    }
}
