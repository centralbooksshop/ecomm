<?php

namespace Retailinsights\EcomCustom\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\ItemFactory as QuoteItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class SaveCustomOptionsToOrder implements ObserverInterface
{
    protected $quoteRepository;
    protected $customerFactory;
    protected $productFactory;
    protected $orderFactory;
    protected $categoryRepository;
    protected $storeManager;
    protected $quoteFactory;
    protected $orderSender;
    protected $checkoutSession;
    protected $scopeConfig;
    protected $resourceConfig;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $addressRepository;
    private $quoteItemFactory;
    private $serializer;

    public function __construct(
        Json $serializer,
        QuoteItemFactory $quoteItemFactory,
        QuoteRepository $quoteRepository,
        CustomerFactory $customerFactory,
        ProductFactory $productFactory,
        OrderFactory $orderFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory,
        OrderSender $orderSender,
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        ResourceConfig $resourceConfig,
        AddressRepositoryInterface $addressRepository,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->customerFactory = $customerFactory;
        $this->quoteRepository = $quoteRepository;
        $this->productFactory = $productFactory;
        $this->orderFactory = $orderFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->addressRepository = $addressRepository;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->serializer = $serializer;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        $this->saveCustomOptionsToOrder($order, $quote);
        return $this;
    }

    public function saveCustomOptionsToOrder(Order $order, Quote $quote)
    {
            if($this->getWebsiteCode() == 'schools'){
				
				$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/given_options_items.log');
				$logger = new \Zend_Log();
				$logger->addWriter($writer);
				
				$quoteRepository = $this->quoteRepository;
                $quote = $quoteRepository->get($order->getQuoteId());
				$allItems = $quote->getAllVisibleItems();
				$bundle_itemId = null;
				$orderItems = $order->getAllVisibleItems();
                foreach ($orderItems as $key => $item) {
					$product_type = $item->getProductType();
					if($product_type == 'bundle') {
						$bundle_product_id = $item->getProductId();
						$bundle_itemId = $item->getItemId(); // parent item_id
						$allItemsCount = count($allItems);
						if($allItemsCount > 0) {
							$optional_selected_items = '';
							foreach($allItems as $quote_item) {
								$product_type = $quote_item->getProductType();
								$quote_item_product_id = $quote_item->getProductId();
						        if($product_type == 'bundle' && $quote_item_product_id == $bundle_product_id) {
								   $optional_selected_items = $quote_item->getOptionalSelectedItems();
								}
							}
						}
                        $final_optional_selected_items = $bundle_product_id.','.$optional_selected_items;
				       //if ($item->getQuoteItemId()) {
					    $item->setOptionalSelectedItems($final_optional_selected_items);
					    continue;
					}
				}
				// Build a map of quote items by product_id (prefer higher given_options like custom_field logic)
				$selectedItemsMap = [];
				foreach ($quote->getAllItems() as $quoteItem) {
					$pid = $quoteItem->getItemId();  // Use quote item ID as key
					$new = (int)$quoteItem->getGivenOptions(); // new value

					if (!isset($selectedItemsMap[$pid])) {
						// First assignment
						$selectedItemsMap[$pid] = $quoteItem;
					} else {
						$current = (int)$selectedItemsMap[$pid]->getGivenOptions();

						// Only prefer higher custom_field (ignore date)
						if ($new > $current) {
							$selectedItemsMap[$pid] = $quoteItem;
						}
					}
				}

				// Loop through order items and update given options for simple bundle children
				foreach ($order->getAllItems() as $orderItem) {
					$quote_item_id = $orderItem->getQuoteItemId(); // Use quote item ID from order
					$productType = $orderItem->getProductType();

					if ($productType === 'simple' && isset($selectedItemsMap[$quote_item_id])) {
						$logger->info('Updating given options for quote_item_id: ' . $quote_item_id);

						// Correct selection using quote_item_id, not product_id
						$selected = $selectedItemsMap[$quote_item_id];

						$orderItem->setGivenOptions($selected->getGivenOptions() ?? '');
						$orderItem->setGivenOptionsMsg($selected->getGivenOptionsMsg() ?? '');
						$orderItem->setGivenOptionUpdatedAt($selected->getGivenOptionUpdatedAt() ?? null);
						$orderItem->setData('dispatch_status', 'not_confirmed');
						$orderItem->setData('delivery_status', 'not_confirmed');
					}
				}


                $order->save();
			}
    }

	public function getWebsiteCode()
    {
        return $this->storeManager->getWebsite()->getCode();
    }
}
