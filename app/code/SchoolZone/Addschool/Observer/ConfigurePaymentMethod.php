<?php

namespace SchoolZone\Addschool\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

 
 
class ConfigurePaymentMethod implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * 
     * 
     */

    protected $_cart;
    protected $_checkoutSession;
    protected $registry;
    protected $_storeManager;
    private $logger;
    private $collectionFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \SchoolZone\Addschool\Model\SimilarproductsattributesFactory $collectionFactory,
        \Retailinsights\Postcode\Model\ResourceModel\Similarproductsattributes\CollectionFactory $pincodeCollection
    )
    {
        $this->registry = $registry;
        $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->pincodeCollection = $pincodeCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
	{
		if ($this->getWebsiteCode() == 'schools') {

			$cartItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();

			// Safe guard for empty cart or split-order temporary quote
			if (empty($cartItems)) {
				return; // do not restrict payment methods
			}

			$productIds = [];
			foreach ($cartItems as $item) {
				$productIds[] = $item->getProduct()->getId();
			}

			// Guard for missing index 0
			if (!isset($productIds[0])) {
				return; // no valid product found
			}

			$productId = $productIds[0];

			// Safe product loading
			try {
				$productCollection = $this->collectionFactory->create()
					->getCollection()
					->addFieldToFilter('school_name', ['neq' => '']); // Just ensure collection initiates

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
				$products = $productFactory->create()
					->addAttributeToSelect('*')
					->addAttributeToFilter('entity_id', ['eq' => $productId])
					->setStore($this->_storeManager->getStore());

				$productData = $products->getFirstItem();

				// Guard if product not found
				if (!$productData || !$productData->getId()) {
					return;
				}

				$catData = $productData->getData();

				// Guard missing school_name
				if (empty($catData['school_name'])) {
					return;
				}

				// Fetch school configuration
				$schoolCollection = $this->collectionFactory->create()
					->getCollection()
					->addFieldToFilter('school_name', $catData['school_name'])
					->setPageSize(1);

				$schoolFilterData = $schoolCollection->getFirstItem()->getData();

			} catch (\Throwable $e) {
				$this->logger->error("ConfigurePaymentMethod Error: " . $e->getMessage());
				return; // Do NOT stop checkout
			}

			// === Payment restriction logic ===
			$methodCode = $observer->getEvent()->getMethodInstance()->getCode();
			$checkResult = $observer->getEvent()->getResult();

			foreach ($schoolFilterData as $key => $value) {
				if ($key == 'enable_payu' && $value == '0' && $methodCode == 'payu') {
					$checkResult->setData('is_available', false);
				}
				if ($key == 'enable_cashfree' && $value == '0' && $methodCode == 'cashfree') {
					$checkResult->setData('is_available', false);
				}
				if ($key == 'enable_ccavenue' && $value == '0' && $methodCode == 'ccavenue') {
					$checkResult->setData('is_available', false);
				}
				if ($key == 'enable_cod' && $value == '0' && $methodCode == 'cashondelivery') {
					$checkResult->setData('is_available', false);
				}
			}

		} else {
			// non-schools site unchanged...
			$methodCode = $observer->getEvent()->getMethodInstance()->getCode();
			if ($methodCode == "cashondelivery") {
				$checkResult = $observer->getEvent()->getResult();
				$checkResult->setData('is_available', false);
			}
			$addressPostcode = $this->_checkoutSession->getQuote()->getShippingAddress()->getPostcode();
			$pinCodes = $this->pincodeCollection->create()
				->addFieldToSelect('*')
				->addFieldToFilter('postcode', $addressPostcode);

			if ($pinCodes && $pinCodes->getFirstItem()->getData('cod_availability') == '1' && ($methodCode == "cashondelivery")) {
				$checkResult = $observer->getEvent()->getResult();
				$checkResult->setData('is_available', true);
			}
		}
	}

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }
}