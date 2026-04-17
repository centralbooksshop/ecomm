<?php
namespace Shipsy\EcommerceExtension\Block;

class Post extends \Magento\Framework\View\Element\Template
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
    protected $_messageManager;

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
        \Magento\Framework\Message\ManagerInterface $messageManager,
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
        $this->_messageManager = $messageManager;
        $this->dataHelper = $dataHelper;
    }
    
    /**
     * @param $orderId
     * @return null|string
     */
    public function getOrderStatus($orderId = null)
    {
        if (empty($orderId)) {
            $orderId = $this->getOrderId();
        }

        return $this->orderRepository->get($orderId);
    }

    public function getItem($itemId)
    {
        return $this->_item->getById($itemId);
    }

    public function getFormAction()
    {
        return $this->_urlInterface->getUrl('/softdatashipsy/formdata');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getServiceTypes()
    {
        try {
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
            $response = json_decode($result, true);
            if (array_key_exists('data', $response) && !empty($response['data'])) {
                return json_encode($response['data']['serviceTypes']);
            } else {
                throw new \Exception($response['error']['message']);
            }
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }


	public function getLocalServiceTypes()
	{
		try {
			// Get Object Manager instance
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			// Create service collection
			$collection = $objectManager
				->create(\Shipsy\EcommerceExtension\Model\ResourceModel\Service\Collection::class);

			// Select only needed fields
			$collection->addFieldToSelect(['entity_id', 'service_code', 'name', 'active', 'is_default']);

			// Add condition: only default = yes (1)
			$collection->addFieldToFilter('is_default', ['eq' => 1]);

			// Build array
			$services = [];
			foreach ($collection as $service) {
				$services[] = [
					'id' => $service->getEntityId(),
					'code' => $service->getServiceCode(),
					'name' => $service->getName(),
					'active' => (int)$service->getActive(),
					'is_default' => (int)$service->getIsDefault()
				];
			}

			// Encode and return JSON
			return json_encode($services);
		} catch (\Exception $e) {
			return json_encode(['error' => $e->getMessage()]);
		}
	}



    public function getAddresses()
    {
        try {
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
            $response = json_decode($result, true);
            if (array_key_exists('data', $response) && !empty($response['data'])) {
                $allAddresses = $response['data'];
                if (!array_key_exists('forwardAddress', $allAddresses) ||
                    !array_key_exists('reverseAddress', $allAddresses) ||
                    !array_key_exists('exceptionalReturnAddress', $allAddresses)
                    ) {
                    return json_encode(['error' => 'Unable to get shop data']);
                } else {
                    return json_encode($response['data']);
                }
            } else {
                throw new \Exception($response['error']['message']);
            }
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getCountryName($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
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
