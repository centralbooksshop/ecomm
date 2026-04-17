<?php
namespace Centralbooks\ClickpostExtension\Block;

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
        \Centralbooks\ClickpostExtension\Helper\Data $dataHelper,
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$clickpost_pickup_address = $resource->getTableName('clickpost_pickup_address');
		$newsql = "SELECT * FROM " . $clickpost_pickup_address . " WHERE id = 1";
		$queryResult = $connection->fetchRow($newsql);
        if(empty($queryResult)){ 
         $address_sql = "INSERT INTO clickpost_pickup_address (id, forward_name, forward_email, forward_phone, forward_alt_phone, forward_line_1, forward_line_2, forward_city, forward_state, forward_country, forward_pincode, forward_tin) VALUES('1','Dora Babu Pindi','dorababupindi@centralbooks.in','7569913780','7569913780','Plot No  1/5B  TSIIC Industrial Development Area  Chilkanagar Chilkanagar X road  Hyderabad - 500040',NULL,'Hyderabad','Telangana','India','500040','36AACFC4622L1ZI');";
		 $insertResult = $connection->query($address_sql);
		}
		//echo '<pre>';print_r($queryResult);die;
        return $queryResult;
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
}
