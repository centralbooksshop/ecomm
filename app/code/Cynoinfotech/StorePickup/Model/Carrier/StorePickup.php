<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;

class StorePickup extends AbstractCarrier implements CarrierInterface
{
    /**
     * Constant for method code
     */
    const METHOD_CODE = 'cistorepickup';
    
    protected $_code = self::METHOD_CODE;
    protected $_isFixed = true;
    protected $rateResultFactory;
    protected $rateMethodFactory;
	private $schoolcollectionFactory;
	protected $storeManager;
	protected $_checkoutSession;
    
    /**
     * Carrier constructor
     *
     * @param ScopeConfigInterface $scopeConfig       Scope Configuration
     * @param ErrorFactory         $rateErrorFactory  Rate error Factory
     * @param LoggerInterface      $logger            Logger
     * @param ResultFactory        $rateResultFactory Rate result Factory
     * @param MethodFactory        $rateMethodFactory Rate method Factory
     * @param array                $data              Carrier Data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
		\SchoolZone\Addschool\Model\SimilarproductsattributesFactory $schoolcollectionFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
		$this->schoolcollectionFactory = $schoolcollectionFactory;
		$this->_storeManager = $storeManager;
		$this->_checkoutSession = $checkoutSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }
    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
		$currentWebsiteCode = $this->getCurrentWebsiteCode();
        if($currentWebsiteCode == 'schools'){
          $is_enable_storepickup = $this->enableStorepickup();
		  if(!$is_enable_storepickup){
			 return false;
		  }
		}
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->getCarrierCode());
        $method->setMethodTitle($this->getConfigData('name'));
        $amount = $this->getConfigData('price');
        $price = $this->getFinalPriceWithHandlingFee($amount);
        $method->setPrice($price);
        $method->setCost($price);
        $result->append($method);
        return $result;
    }

	public function enableStorepickup()
	{
		$productId = [];

		// get cart items - guard if session or quote missing
		$quote = $this->_checkoutSession->getQuote();
		if (!$quote) {
			return false;
		}

		$cartData = $quote->getAllVisibleItems();
		if (empty($cartData)) {
			// no items in quote -> do not enable store pickup
			return false;
		}

		foreach ($cartData as $key => $value) {
			$product = $value->getProduct();
			if ($product && $product->getId()) {
				$productId[] = $product->getId();
			}
		}

		if (empty($productId) || !isset($productId[0])) {
			return false;
		}

		// avoid fatal if store manager or product factory missing (keep original behaviour)
		try {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
			$productscoll = $productFactory->create()
				->addAttributeToSelect('*')
				->addAttributeToFilter('entity_id', ['eq' => $productId[0]])
				->setStore($this->_storeManager->getStore());

			$productsdata = $productscoll->getFirstItem();
			if (!$productsdata || !$productsdata->getId()) {
				return false;
			}

			$cartDataArr = $productsdata->getData();
			if (empty($cartDataArr) || !isset($cartDataArr['school_name'])) {
				return false;
			}

			$schoolCollection = $this->schoolcollectionFactory->create();
			$filter = $schoolCollection->getCollection()
				->addFieldToFilter('school_name', $cartDataArr['school_name'])
				->setPageSize(1);

			$schoolFilterData = $filter->getFirstItem()->getData();
			if (empty($schoolFilterData) || !isset($schoolFilterData['enable_storepickup'])) {
				return false;
			}

			$enable_storepickup = $schoolFilterData['enable_storepickup'];
			return (!empty($enable_storepickup) && $enable_storepickup == '1');
		} catch (\Throwable $e) {
			// log if you want, but do not break checkout flow
			$this->_logger->info('enableStorepickup exception: ' . $e->getMessage());
			return false;
		}
	}


	  public function getCurrentWebsiteCode()
      {
        return $this->_storeManager->getWebsite()->getCode();
       }
}
