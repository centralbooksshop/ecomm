<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Webkul\DeliveryBoy\Model\Rating\Ratingstatus;
use Webkul\DeliveryBoy\Model\Deliveryboy\Source\ApproveStatus;
use Magento\Backend\Model\Auth\Session as AdminSession;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_LATITUDE = 'deliveryboy/configuration/latitude';
    public const XML_PATH_LONGITUDE = 'deliveryboy/configuration/longitude';
    public const XML_PATH_ALLOWED_SHIPPING = "deliveryboy/configuration/allowed_shipping";
    public const XML_PATH_ADMIN_EMAIL = "deliveryboy/configuration/admin_email";
    public const XML_PATH_PAGE_SIZE = "deliveryboy/configuration/pagesize";
    public const XML_PATH_LOCALE_CODE = "general/locale/code";
    public const XML_PATH_FCM_AUTHKEY = "deliveryboy/auth/apikey";
    public const XML_PATH_FORGOT_PASSWORD_TEMPLATE = "deliveryboy/email/deliveryboy_forgotpassword_validation_template";
    public const XML_PATH_FORGOT_GENERAL_EMAIL = "trans_email/ident_general/email";
    public const XML_PATH_GOOGLE_MAP_KEY = 'deliveryboy/configuration/map_key';
    public const XML_PATH_WAREHOUSE_ADDRESS = "deliveryboy/configuration/warehouse_address";
    public const XML_PATH_ANONYMOUS_DELIVERYBOY_REGISTRATION =
         "deliveryboy/configuration/enable_anonymous_deliveryboy_registration";

    /**
     * @var string
     */
    protected $_deploymentConfigDate;
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_storeTime;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResourceCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Operation
     */
    private $operationHelper;

    /**
     * @var Ratingstatus
     */
    private $ratingStatus;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param DeploymentConfig $deploymentConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Webkul\DeliveryBoy\Model\VehicleType\Source\Option $vehicleTypeOptions
     * @param Operation $operationHelper
     * @param RatingStatus $ratingStatus
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Encryption\EncryptorInterface $enc
     * @param \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboyF
     * @param \Magento\Customer\Model\CustomerFactory $customerF
     * @param AdminSession $adminSession
     * @param \Magento\Review\Helper\Data $reviewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        DeploymentConfig $deploymentConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Webkul\DeliveryBoy\Model\VehicleType\Source\Option $vehicleTypeOptions,
        Operation $operationHelper,
        RatingStatus $ratingStatus,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Encryption\EncryptorInterface $enc,
        \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboyF,
        \Magento\Customer\Model\CustomerFactory $customerF,
        AdminSession $adminSession,
        \Magento\Review\Helper\Data $reviewHelper,
        array $data = []
    ) {
        parent::__construct($context);
        
        $this->scopeConfig = $context->getScopeConfig();
        $this->resource = $resource;
        $this->request =  $request;
        $this->_storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->_storeTime = $timezone;
        $this->_orderFactory = $orderFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
        $this->_deploymentConfigDate = $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE
        );
        $this->priceCurrency = $priceCurrency;
        $this->operationHelper = $operationHelper;
        $this->ratingStatus = $ratingStatus;
        $this->vehicleTypeOptions = $vehicleTypeOptions;
        $this->deliveryboyF = $deliveryboyF;
        $this->enc = $enc;
        $this->customerF = $customerF;
        $this->logger = $logger;
        $this->adminSession = $adminSession;
        $this->reviewHelper = $reviewHelper;
    }

    /**
     * Function verify the string is json or not
     *
     * @param string $string to check wheather it is json or not
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Function to get order count assigned to deliveryboy
     *
     * @param string $to end range of date
     * @param string $from start range of date
     * @return array
     */
    public function filterCollection($to, $from)
    {
        $data = [];
        $tableName = $this->resource->getTableName("sales_order_grid");
        $collection = $this->deliveryboyOrderResourceCollectionFactory->create()->load();
        $collection->getSelect()->where("created_at <= " . $to . " AND created_at >= " . $from);
        foreach ($collection as $record) {
            $collectionData = $this->_orderFactory->create()->getCollection()
                ->addAttributeToSelect("entity_id")
                ->addAttributeToSelect("increment_id")
                ->addAttributeToSelect("created_at")
                ->addAttributeToSelect("grand_total")
                ->addAttributeToFilter("entity_id", $record->getOrderId())->getFirstItem();

            $eachOrder = $collectionData->getData();
            $oneOrder = [];
            $data[] = $collectionData->getData();
        }
        return $data;
    }

    /**
     * Get deliveryboy order sales.
     *
     * @param string $dateType
     * @return array
     */
    public function getSale($dateType = 'year')
    {
        $data = [];
        if ($dateType == 'year') {
            $data = $this->getYearlySale();
        } elseif ($dateType == 'month') {
            $data = $this->getMonthlySale();
        } elseif ($dateType == 'week') {
            $data = $this->getWeeklySale();
        } elseif ($dateType == 'day') {
            $data = $this->getDailySale();
        }

        return $data;
    }

    /**
     * Get sales graph url
     *
     * @param string $data
     * @return string
     */
    public function getSalesAmount($data)
    {
        $returnArray = $this->getSale($data);
        $params = [
            'cht'   =>    'lc',
            'chm'   =>    'B,BFCFFF,0,-1,11',
            'chf'   =>    'bg,s,ffffff',
            'chxt'  =>   'x,y',
            'chds'  =>   'a',
            'chdl'  =>  'sales',
            'chem'  =>  'y',
            'chco'  =>  '76A4FB',
            'chbh'  =>   '55'
        ];
        $getSale = $returnArray['values'] ?? "";

        if (isset($returnArray['arr'])) {
            $arr = $returnArray['arr'];
            $indexid = 0;
            $tmpstring = implode('|', $arr);
            $valueBuffer[] = $indexid . ':|' . $tmpstring;
            $valueBuffer = implode('|', $valueBuffer);
            $params['chxl'] = $valueBuffer;
        } else {
            $params['chxl'] = $returnArray['chxl'] ?? "";
        }
        $params['chd'] = (!empty($getSale)) ? 't:' . implode(',', $getSale) : 't:';
        $valueBuffer = [];
        // seller statistics graph size
        $params['chs'] = 800 . 'x' . 370;
        // return the encoded graph image url
        $getParamData = urlencode(base64_encode(json_encode($params)));
        $getEncryptedHashData = $this->operationHelper->getChartEncryptedHashData(
            $getParamData
        );
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData
        ];
        return $this->urlInterface->getUrl(
            'expressdelivery/graph/generateGraph',
            ['_query' => $params, '_secure' => $this->request->isSecure()]
        );
    }

    /**
     * Get year sales data according to year fiter
     *
     * @return array
     */
    public function getYearlySale()
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $monthsArr = [
            '',
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        ];
        for ($i = 1; $i <= $currMonth; ++$i) {
            $date1 = $curryear . '-' . $i . '-01 00:00:00';
            $date2 = $curryear . '-' . $i . '-31 23:59:59';
            $to = $curryear . "-12-31 23:59:59";
            $from = $curryear . "-01-01 00:00:00";
            $collection = $this->filterCollection($to, $from);
            $totalSaleAmount = 0;
            foreach ($collection as $record) {
                $totalSaleAmount = $totalSaleAmount + ($record["grand_total"]);
            }
            $data['values'][$i] = $this->getCurrentAmount($totalSaleAmount);
            if ($i != $currMonth) {
                $data['chxl'] = $data['chxl'] . $monthsArr[$i] . '|';
            } else {
                $data['chxl'] = $data['chxl'] . $monthsArr[$i];
            }
            $data['totalsale'] = $totalSaleAmount;
        }
        return $data;
    }

    /**
     * Get month sales data according to month filter
     *
     * @return array
     */
    public function getMonthlySale()
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        for ($i = 1; $i <= $currDays; ++$i) {
            $date1 = $curryear . '-' . $currMonth . '-' . $i . ' 00:00:00';
            $date2 = $curryear . '-' . $currMonth . '-' . $i . ' 23:59:59';

            $to = $curryear . "-" . $currMonth . "-" . $currDays . " 23:59:59";
            $from = $curryear . "-" . $currMonth . "-01 00:00:00";
            $salesCollection = $this->filterCollection($to, $from);

            $sum = [];
            $totalSales = 0;
            foreach ($salesCollection as $record) {
                $totalSales = $totalSales + $record["grand_total"];
            }
            $price = $totalSales;
            if ($price * 1 && $i != $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'] .
                $currMonth . '/' .
                $i . '/' .
                $curryear . '|';
            } elseif ($i < 5 && $price * 1 == 0 && $i != $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'] .
                $currMonth . '/' . $i . '/' .
                $curryear . '|';
            }
            if ($i == $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] =
                $data['chxl'] .
                $currMonth . '/' .
                $i . '/' . $curryear;
            }
        }
        return $data;
    }

    /**
     * Get weekly sales data according to weekly sales
     *
     * @return array
     */
    public function getWeeklySale()
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        $currWeekDay = date('N');
        $currWeekStartDay = $currDays - $currWeekDay;
        $currWeekEndDay = $currWeekStartDay + 7;
        $currentDayOfMonth=date('j');

        $startDate = strtotime("-" . ($currWeekDay-1) . " days", time());
        $prevyear = date("Y", $startDate);
        $prevMonth = date("m", $startDate);
        $prevDay = date("d", $startDate);

        if ($currWeekEndDay > $currentDayOfMonth) {
            $currWeekEndDay = $currentDayOfMonth;
        }
        for ($i = $currWeekStartDay + 1; $i <= $currWeekEndDay; ++$i) {
            $date1 = $curryear . '-' . $currMonth . '-' . $i . ' 00:00:00';
            $date2 = $curryear . '-' . $currMonth . '-' . $i . ' 23:59:59';

            $to = $curryear . "-" . $currMonth . "-" . $currDays . " 23:59:59";
            $from = $prevyear . "-" . $prevMonth . "-" . $prevDay . " 00:00:00";

            $collection = $this->filterCollection($to, $from);
            $sum = [];
            $temp = 0;
            foreach ($collection as $record) {
                $temp = $temp + $record["grand_total"];
            }
            $price = $temp;
            if ($i != $currWeekEndDay) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'] .
                $currMonth . '/' . $i .
                '/' . $curryear . '|';
            }
            if ($i == $currWeekEndDay) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'] .
                $currMonth . '/' .
                $i . '/' . $curryear;
            }
        }
        return $data;
    }

    /**
     * Get currenct day sales collection
     *
     * @return array
     */
    public function getDailySale()
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        $currTime = date('G');
        $arr = [];
        $k = 0;
        for ($i = 0; $i <= 23; $i++) {
            $date1 = $curryear . '-' . $currMonth . '-' . $currDays . ' ' . $i . ':00:00';
            $updatedDate1 = $this->_storeTime->convertConfigTimeToUtc($date1);
            $j = $i+2;
            $date2 = $curryear . '-' . $currMonth . '-' . $currDays . ' ' . $j . ':59:59';
            $updatedDate2 = $this->_storeTime->convertConfigTimeToUtc($date2);
            $to = $curryear . "-" . $currMonth . "-" . $currDays . " 23:59:59";
            $from = $curryear . "-" . $currMonth . "-" . $currDays . " 00:00:00";
            $collection = $this->filterCollection($to, $from);
            $sum = [];
            $totalSales = 0;
            foreach ($collection as $record) {
                $totalSales = $totalSales + $record["grand_total"];
            }
            $price = $totalSales;
            if ($i != 23) {
                $data['values'][$k] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'] .
                date("g:i A", strtotime($date2)) . '|';
            }
            if ($i == 23) {
                $data['values'][$k] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'] . date(
                    "g:1 A",
                    strtotime($date2)
                );
            }
            $i = $j;
            $k++;
        }
        return $data;
    }

    /**
     * Format amount in current store current currency
     *
     * @param float $amount
     * @return float
     */
    public function getCurrentAmount($amount)
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $store = $this->_storeManager->getStore()->getStoreId();
        return $priceCurrencyObject->convert($amount, $store, $currency);
    }

    /**
     * Get Warehouse latitude.
     *
     * @return float
     */
    public function getLatitude(): float
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_LATITUDE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get warehouse longitude.
     *
     * @return float
     */
    public function getLongitude(): float
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_LONGITUDE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get allowed shipping methods for deliveryboy orders.
     *
     * @return string
     */
    public function getAllowedShipping()
    {
        return ((string)$this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) ?: "";
    }

    /**
     * Get Config data.
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigData(string $path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin email address.
     *
     * @return string
     */
    public function getAdminEmail(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is anonymous deliveryboy registration enabled.
     *
     * @return bool
     */
    public function isEnabledAnonymousDeliveryboyRegistration()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ANONYMOUS_DELIVERYBOY_REGISTRATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get collection page size.
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAGE_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get local codes.
     *
     * @param int $storeId
     * @return string
     */
    public function getLocaleCodes(int $storeId): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOCALE_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get FCM api key.
     *
     * @return string
     */
    public function getFcmApiKey(): ?string
    {
        $key = $this->scopeConfig->getValue(
            self::XML_PATH_FCM_AUTHKEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->enc->decrypt($key);
    }

    /**
     * Get forgot password email template Id.
     *
     * @return string
     */
    public function getForgotPasswordTemplate(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FORGOT_PASSWORD_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin general email address.
     *
     * @return string
     */
    public function getGeneralEmail(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FORGOT_GENERAL_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get google map key.
     *
     * @return string
     */
    public function getGoogleMapKey(): ?string
    {
        $key = $this->scopeConfig->getValue(
            self::XML_PATH_GOOGLE_MAP_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->enc->decrypt($key);
    }

    /**
     * Get warehouse address.
     *
     * @return string
     */
    public function getWarehouseAddress(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WAREHOUSE_ADDRESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Format date and time locally.
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatDateTimeCurrentLocale(
        string $date,
        string $format = "M d, Y h:i:s A"
    ): string {
        $adminDate = $this->_storeTime->formatDate(
            new \DateTime($date),
            \IntlDateFormatter::MEDIUM,
            true
        );
        return $adminDate;
    }

    /**
     * Get order invalid states.
     *
     * @return array
     */
    public function getOrderInvalidStates(): array
    {
        return [
            //\Magento\Sales\Model\Order::STATE_COMPLETE,
		    \Magento\Sales\Model\Order::STATE_PROCESSING,
            \Magento\Sales\Model\Order::STATE_CLOSED,
            \Magento\Sales\Model\Order::STATE_CANCELED,
        ];
    }

    /**
     * Get rating statuses.
     *
     * @return array
     */
    public function getRatingStatuses(): array
    {
        $ratingStatuses = $this->ratingStatus->toOptionArray();
        $parsedRatingStatues = [];
        foreach ($ratingStatuses as $ratingStatus) {
            $parsedRatingStatues[$ratingStatus['value']] = $ratingStatus['label'];
        }
        return $parsedRatingStatues;
    }

    /**
     * Function to get Url of directory
     *
     * @param string $dir directory
     *
     * @return string|null
     */
    public function getUrl(string $dir): ?string
    {
        return $this->_storeManager->getStore()->getBaseUrl($dir);
    }

    /**
     * Can deliveryboy be assigned to this order.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canAssignOrder($order)
    {
        //if ((!in_array($order->getState(), $this->getOrderInvalidStates())) && $order->canShip()) {
	    if ((!in_array($order->getState(), $this->getOrderInvalidStates()))) {
            return true;
        }

        return false;
    }

    /**
     * Is order allowed for deliveryboy shipping.
     *
     * @param string $orderShippingMethod
     * @return bool
     */
    public function isAllowedForShipping($orderShippingMethod)
    {
        $allowedShipping = explode(',', $this->getAllowedShipping());
        $allowedShipping = array_map(function ($item) {
            $isContainUnderScore = strpos($item, '_') !== false;
            $method = explode('_', $item)[0];
            return $isContainUnderScore ? $method.'_' : $method;
        }, $allowedShipping);
        $isContainUnderScore = strpos($orderShippingMethod, '_') !== false;
        $method = explode('_', $orderShippingMethod)[0];
        $orderShippingMethod = $isContainUnderScore ? $method.'_' : $method;
        return in_array($orderShippingMethod, $allowedShipping);
    }

    /**
     * Generate random transaction string.
     *
     * @param OrderInterface $deliveryboyOrder
     * @return string
     */
    public function generateDeliveryboyOrderTransactionId($deliveryboyOrder)
    {
        $deliveryboyOrderId = $deliveryboyOrder->getId();
        $orderIncrementId = $deliveryboyOrder->getIncrementId();
        $orderId = $deliveryboyOrder->getOrderId();
        $transactionId = $deliveryboyOrderId . ':' . $orderId . ':' . $deliveryboyOrderId;
        $transactionId = sha1($transactionId);
        return $transactionId;
    }

    /**
     * Get available deliveryboy vehicle types.
     *
     * @return void
     */
    public function getVehicleTypeList()
    {
        return $this->vehicleTypeOptions->toOptionArray();
    }

    /**
     * Approve deliveryboy
     *
     * @param int $deliveryboyId
     * @return void
     */
    public function approveDeliveryboy($deliveryboyId)
    {
        $deliveryboy = $this->deliveryboyF->create()->load($deliveryboyId);
        $deliveryboy->setApproveStatus(ApproveStatus::STATUS_APPROVED)->save();
        $this->sendDeliveryboyApprovedEmail($deliveryboyId);
    }

    /**
     * Send Approved email to delvieryboy.
     *
     * @param int $deliveryboyId
     * @return void
     */
    public function sendDeliveryboyApprovedEmail($deliveryboyId)
    {
        try {
            $storeId = $this->_storeManager->getStore()->getId();
            $deliveryBoy = $this->deliveryboyF->create()->load($deliveryboyId);
            $templateVars = [
                'deliveryboyName' => $deliveryBoy->getName(),
                'deliveryboyEmail' => $deliveryBoy->getEmail(),
            ];
            $senderInfo = $this->getSenderInfoForDeliveryboyRegistration($deliveryBoy);
            $receieverEmail = $deliveryBoy->getEmail();

            $receiversInfo = [
                'name' => $deliveryBoy->getName(),
                'email' => $receieverEmail,
            ];
            $templateId = ModuleGlobalConstants::DELIVEYBOY_APPROVED_TEMPLATE_ID;
            $this->operationHelper->sendEmail(
                $storeId,
                $templateVars,
                $senderInfo,
                $receiversInfo,
                $templateId
            );
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
    }

    /**
     * Send new registration email to deliveryboy
     *
     * @param int $deliveryboyId
     * @return void
     */
    public function sendDeliveryboyRegistrationEmail($deliveryboyId)
    {
        try {
            $storeId = $this->_storeManager->getStore()->getId();
            $deliveryBoy = $this->deliveryboyF->create()->load($deliveryboyId);
            $templateVars = [
                'deliveryboyName' => $deliveryBoy->getName(),
                'deliveryboyEmail' => $deliveryBoy->getEmail(),
                'ownerName' => $this->getDeliveryboyOwnerName($deliveryBoy),
            ];
            $senderInfo = $this->getSenderInfoForDeliveryboyRegistration($deliveryBoy);
            $receieverEmail = $deliveryBoy->getEmail();
            $receiversInfo = [
                'name' => $deliveryBoy->getName(),
                'email' => $receieverEmail,
            ];
            $templateId = ModuleGlobalConstants::DELIVEYBOY_REGISTRATION_NOTIFICATION_TEMPLATE_ID;
            $this->operationHelper->sendEmail(
                $storeId,
                $templateVars,
                $senderInfo,
                $receiversInfo,
                $templateId
            );
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
    }

    /**
     * Get deliveryboy owner name.
     *
     * @param Deliveyrboy $deliveryboy
     * @return string
     */
    public function getDeliveryboyOwnerName($deliveryboy)
    {
        $adminEmail = $this->getAdminEmail();
        $owner = $this->customerF->create()->getCollection()
            ->addFieldToFilter('email', $adminEmail)
            ->getFirstItem();
        return $owner->getName() . ' (' . __('Administrator') . ')';
    }

    /**
     * Get Sender's name and email.
     *
     * @param [type] $deliveryboy
     * @return void
     */
    public function getSenderInfoForDeliveryboyRegistration($deliveryboy)
    {
        return [
            'email' => $this->getAdminEmail(),
            'name' => $this->getDeliveryboyOwnerName($deliveryboy),
        ];
    }

    /**
     * Is not a demo user.
     *
     * @return bool
     */
    public function isNotDemoUser()
    {
        $user = $this->adminSession->getUser();
        $role = $user->getRole();
        $roleName = $role->getRoleName();
        $roleName = strtolower($roleName);
        return "demo" !== $roleName;
    }

    /**
     * Get message to be shown to the demo users.
     *
     * @return void
     */
    public function getDemoUserMessage()
    {
        return __("For demo users, API Authentication group is not editable.");
    }

    /**
     * Get Admin owner id.
     *
     * @return int
     */
    public function getAdminId()
    {
        return 0;
    }

    /**
     * Get Deliveryboy review status label.
     *
     * @param string $status
     * @return string
     */
    public function getReviewStatusLabelByReviewStatus($status)
    {
        $statuses = $this->reviewHelper->getReviewStatuses();
        return $statuses[$status];
    }

    /**
     * Get Deliveryboy.
     *
     * @param int $id
     * @return DeliveryBoy
     */
    public function getDeliveryboyModelById($id)
    {
        return $this->deliveryboyF->create()->load($id);
    }
    
    /**
     * Get Customer by id.
     *
     * @param int $id
     * @return Customer
     */
    public function getCustomerModelById($id)
    {
        return $this->customerF->create()->load($id);
    }
}
