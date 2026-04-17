<?php
namespace Centralbooks\ClickpostExtension\Block;

class Post extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
	 /**
	* @var Curl
	*/
    protected $curl;
    protected $orderRepository;
    protected $_item;
    protected $_renderer;
    protected $urlInterface;
    protected $formKey;
    protected $_countryFactory;
    protected $_cookieManager;
    protected $_messageManager;
	
	/** @var \Magento\Sales\Api\Data\OrderInterface $order **/

    protected $order;

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
        \Centralbooks\ClickpostExtension\Helper\Data $dataHelper,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Sales\Api\Data\OrderInterface $order,

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
		$this->curl = $curl;
		$this->order = $order;
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

    public function getRecommendationApi()
    {
        try {
           $clickpost_username = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           $clickpost_key = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           $clickpost_returnaddress = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_return_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $params = $this->getRequest()->getParams();
		    $orderIds = json_decode($params['id']);
		    $orderid = $orderIds[0];
			$order = $this->order->load($orderid); 
			$increment_id = $order->getIncrementId();
			$billingAddress = $order->getBillingAddress()->getData();
            $shippingAddress = $order->getShippingAddress()->getData();
			//echo '<pre>';print_r($shippingAddress);die;
			$postcode = $shippingAddress['postcode'];
			$region = $shippingAddress['region'];
			$invoicevalue = $order->getGrandTotal();
		    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$clickpost_pickup_address = $resource->getTableName('clickpost_pickup_address');
           
		    $addresssql = "SELECT id FROM " . $clickpost_pickup_address . " WHERE forward_state = "."'$region'";
		     $addressres = $connection->fetchRow($addresssql);
		     if(!empty($addressres['id']))
			 {
			   $address_id = $addressres['id'];
             }
			$pickup_address_sql = "SELECT * FROM " . $clickpost_pickup_address . " WHERE id = "."'$address_id'";
			$pickup_addressResult = $connection->fetchRow($pickup_address_sql);
			$pickup_pincode = $pickup_addressResult['forward_pincode'];
		    //echo '<pre>';print_r($shippingAddress);die;

		    $postarrayData= [[
		     'pickup_pincode'=> $pickup_pincode,
		     'drop_pincode'=> $postcode,
			 'order_type'=>'PREPAID',
		     'reference_number'=>$increment_id,
			 'item'=>'books',
		     'invoice_value'=> round($invoicevalue),
			 'delivery_type'=>'FORWARD',
		     'additional'=> []
			]];

			 $jsonData = json_encode($postarrayData, TRUE);
			 //echo '<pre>';print_r($jsonData);
			 //$jsondecodeData = json_decode($jsonData, TRUE);
			  //echo '<pre>';print_r($jsondecodeData);
              $recommendation_api_url = 'https://www.clickpost.in/api/v1/recommendation_api/?key='.$clickpost_key;
			  //$username = 'username';
			  //$password = 'password';
			  //set curl options
			  //$this->curl->setOption(CURLOPT_USERPWD, $username . ":" . $password);
			  $this->curl->setOption(CURLOPT_HEADER, 0);
			  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
			  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
			  //set curl header
			  $this->curl->addHeader("Content-Type", "application/json");
			  //post request with url and data
			  $this->curl->post($recommendation_api_url, $jsonData);
			  //read response
			  $result = $this->curl->getBody();
			  $response = json_decode($result, true);
			  return $response ;
			 //echo '<pre>';print_r($response);die;

        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getAddresses()
    {
           $params = $this->getRequest()->getParams();
		   $orderIds = json_decode($params['id']);
		    $orderid = $orderIds[0];
			$order = $this->order->load($orderid); 
			$increment_id = $order->getIncrementId();
			$billingAddress = $order->getBillingAddress()->getData();
            $shippingAddress = $order->getShippingAddress()->getData();
			//echo '<pre>';print_r($shippingAddress);die;
			$region = $shippingAddress['region'];
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$clickpost_pickup_address = $resource->getTableName('clickpost_pickup_address');
           
		     $addresssql = "SELECT id FROM " . $clickpost_pickup_address . " WHERE forward_state = "."'$region'";
		     $addressres = $connection->fetchRow($addresssql);
		     if(!empty($addressres['id']))
			 {
			   $address_id = $addressres['id'];
             }

		$pickup_address_sql = "SELECT * FROM " . $clickpost_pickup_address . " WHERE id = "."'$address_id'";
		$queryResult = $connection->fetchRow($pickup_address_sql);
		//echo '<pre>';print_r($queryResult);die;
        return $queryResult;   
    }

    public function getCountryName($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
}
