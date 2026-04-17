<?php

namespace Retailinsights\Orders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartItemRepositoryInterface;
 
class CheckoutAddStudentInfoToOrder implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * 
     * 
     */

    // protected $_cart;
    protected $_checkoutSession;
    // protected $productRepository;
    protected $registry;
    // protected $_storeManager;
    // protected $productCategory;
    private $logger;
    protected $customerSession;
    private $_request;
    private $collectionFactory;
    protected $quoteItemRepository;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order, 
        \SchoolZone\Addschool\Model\SimilarproductsattributesFactory $collectionFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        // \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        // ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        // ProductCategoryList $productCategory,
        \Psr\Log\LoggerInterface $logger,
        CartItemRepositoryInterface $quoteItemRepository
    )
    {
        $this->_order = $order;   
        $this->collectionFactory = $collectionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->_request = $request;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        // $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        // $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        // $this->productCategory = $productCategory;
        $this->logger = $logger;
        $this->quoteItemRepository = $quoteItemRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       
		
		$action = $this->_request->getFullActionName();

		// Skip logic when item is deleted
		if (in_array($action, [
			'checkout_cart_delete',
			'checkout_cart_updatePost',
			'checkout_cart_deletePost'
		])) {
			return;
		}

		
		if($this->getWebsiteCode() == 'schools') {
			$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/optional_selected_items.log');
			$logger = new \Zend_Log();
			$logger->addWriter($writer);

			$param_product_id = '';
			$product_id = '';
			$param_product_id = $this->_request->getParam('product');
			$allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
			$allItemsCount = count($allItems);
            if($allItemsCount > 0) {
				$bundle_item_product_id = '';
				foreach($allItems as $item) {
					//echo '<pre>';print_r($item->getData());
					$product_type = $item->getProductType();
					if($product_type == 'bundle') {
					   $bundle_item_product_id = $item->getProductId();
					}
				}

				if(!empty($param_product_id)) {
                   $product_id = $param_product_id;
				} else {
                    $product_id = $bundle_item_product_id;
				}
				$optional_selected_items = '';
                $optional_selected_items = $this->getBundleProductOptionsData($product_id);
				$given_selected_items = $this->getBundleProductGivenData($product_id);
				
				$quoteId = $this->_checkoutSession->getQuote()->getId();
				$logger->info('quoteId '.$quoteId);
				$logger->info('product_id '.$product_id);
				$logger->info('optional_selected_items '.$optional_selected_items);
				$logger->info('given_selected_items '.print_r($given_selected_items, true));

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
				//echo '<pre>';print_r($product->getData());die;
				$school_id = $product->getSchoolName();
				$product_purchased = $product->getName();

                $schoolCollection = $this->collectionFactory->create(); 
                $schooldata = $schoolCollection->getCollection()
                    ->addFieldToFilter('school_name', $school_id);
                $schoolFilterData =  $schooldata->getFirstItem()->getData();
               
                //$student_name = $this->_request->getPost('student_pname');
                //$student_number = $this->_request->getPost('student_pid');

				$student_name   = trim((string)$this->_request->getPost('student_pname'));
                $student_number = trim((string)$this->_request->getPost('student_pid'));

				if ($student_name === '') {
					throw new \Magento\Framework\Exception\LocalizedException(
						__('Student Name is required.')
					);
				}

         
                $willbegiven_msg = $schoolFilterData['willbegiven'];
				$schoolgiven_msg = $schoolFilterData['schoolgiven'];
				$school_id = $schoolFilterData['school_name'];
                $school_name = $schoolFilterData['school_name_text'];
                $school_code = $schoolFilterData['school_code'];
				$location_code = $schoolFilterData['location_code'];

				if (!empty($quoteId)) {
					$quote = $this->quoteRepository->get($quoteId);

					// Step 1: Find the parent bundle item ID
					$bundle_itemId = null;
					foreach ($quote->getAllVisibleItems() as $itemq) {
						$item_product_id = $itemq->getProductId();
						if ($item_product_id == $product_id) {
							$bundle_itemId = $itemq->getItemId(); // parent item_id
							$itemq->setOptionalSelectedItems($optional_selected_items);
							$itemq->save();
						}
					}

					// Step 2: Group all given values by product_id + option_id (keep all, don't merge)
					$selectionGrouped = [];
					foreach ($given_selected_items as $val) {
						$pid      = (int)$val['selection_product_id'];
						$optId    = (int)($val['option_id'] ?? 0);

						$selectionGrouped[$pid][$optId][] = $val;
					}

					// Step 3: Loop through all quote items and apply values one by one
				    $selectionUsedIndex = []; // track which index used per (product_id + option_id)

					foreach ($quote->getAllItems() as $itemq) {
						$item_product_id = (int)$itemq->getProductId();
						$parentItemId    = $itemq->getParentItemId();

						// Only children of this bundle
						if ($parentItemId == $bundle_itemId) {

							// 1) Get bundle option_id for this quote item
							$optionId = null;
							$bundleAttrOption = $itemq->getOptionByCode('bundle_selection_attributes');
							if ($bundleAttrOption) {
								try {
									$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
									$serializer = $objectManager->get(\Magento\Framework\Serialize\Serializer\Json::class);
									$attrs = $serializer->unserialize($bundleAttrOption->getValue());
									$optionId = isset($attrs['option_id']) ? (int)$attrs['option_id'] : null;
								} catch (\Exception $e) {
									$logger->info('Error unserializing bundle_selection_attributes: ' . $e->getMessage());
								}
							}

							// 2) If we have mapping for this product + option_id
							if ($optionId !== null 
								&& isset($selectionGrouped[$item_product_id]) 
								&& isset($selectionGrouped[$item_product_id][$optionId])) {

								$key = $item_product_id . '-' . $optionId;

								$itemIndex = $selectionUsedIndex[$key] ?? 0;
								$list      = $selectionGrouped[$item_product_id][$optionId];

								// If somehow more quote children than defined, keep using last entry
								if (!isset($list[$itemIndex])) {
									$itemIndex = count($list) - 1;
								}

								$selected = $list[$itemIndex];
								$selectionUsedIndex[$key] = $itemIndex + 1;

								$logger->info(
									'Assigning product_id ' . $item_product_id .
									' (option_id ' . $optionId . ') -> custom_field = ' . $selected['custom_field']
								);

								// 3) Apply values
								$itemq->setBundleOptionId($optionId);
								$itemq->setGivenOptions($selected['custom_field']);
								if ((int)$selected['custom_field'] === 1) {
									$itemq->setGivenOptionsMsg($willbegiven_msg);
								} elseif ((int)$selected['custom_field'] === 2) {
									$itemq->setGivenOptionsMsg($schoolgiven_msg);
								} else {
									$itemq->setGivenOptionsMsg(null);
								}
								$itemq->setGivenOptionUpdatedAt($selected['updated_at']);
							}
						}

						// Keep your existing split parent logic
						$item_product_type = $itemq->getProductType();
						if ($item_product_type == 'bundle') {
							$bundle_item_product_id = $itemq->getProductId();
						}
						$itemq->setSplitParentItemId($bundle_item_product_id);
					}

					$quote->save();
				}


				
				$nquoteId = $this->_checkoutSession->getQuote()->getId();
                if(($school_id!='') && ($school_code !='') && ($nquoteId != '')) {
                    $quote = $this->quoteRepository->get($nquoteId); 
					$quote->setData('student_name', $student_name);
					$quote->setData('roll_no', $student_number);
					$quote->setData('school_id', $school_id);
					$quote->setData('school_name', $school_name);
					$quote->setData('school_code', $school_code);
					$quote->setData('location_code', $location_code);
					$quote->setData('customer_is_guest', '0');
					$quote->setData('product_purchased', $product_purchased);
					foreach ($quote->getAllVisibleItems() as $quoteItem) {
                       
							$quoteItem_product_id = $quoteItem->getProductId();
							if($quoteItem_product_id == $product_id) {
                                $quoteItem->setData('student_name', $student_name);
                                $quoteItem->setData('roll_no', $student_number);
                                $this->quoteItemRepository->save($quoteItem);
							}
                        //}
                    }
					$this->quoteRepository->save($quote);                    
                }
				
            }
        }
    }

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }

	public function getBundleProductOptionsData($parent_product_id)
    {
		$selection_product_ids = [];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$catalog_product_bundle_option_table = $resource->getTableName('catalog_product_bundle_option'); 
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($parent_product_id);
		$selectionCollection = $product->getTypeInstance(true)
			->getSelectionsCollection(
				$product->getTypeInstance(true)->getOptionsIds($product),
				$product
			);
		foreach ($selectionCollection as $proselection) {
			$selection_product_id = $proselection->getProductId();
			$selection_option_id = $proselection->getOptionId();
			$selection_is_default = $proselection->getIsDefault();
			$bundle_option_sql = "select required from " . $catalog_product_bundle_option_table . " where parent_id = ". $parent_product_id ." AND option_id = ". $selection_option_id ;
			$bundle_option_result = $connection->fetchRow($bundle_option_sql);
			$option_required = $bundle_option_result['required'];
			if($selection_is_default == true && $option_required == true) {
			  $selection_product_ids[] = $selection_product_id;
			}

		}
		$optional_selected_items = implode(",",$selection_product_ids);
		return $optional_selected_items ;
    }

	public function getBundleProductGivenDatadup($parent_product_id)
    {
		$selection_result = [];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$catalog_product_bundle_option_table = $resource->getTableName('catalog_product_bundle_option'); 
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($parent_product_id);
		$selectionCollection = $product->getTypeInstance(true)
			->getSelectionsCollection(
				$product->getTypeInstance(true)->getOptionsIds($product),
				$product
			);
		foreach ($selectionCollection as $proselection) {
			$selection_custom_field = $proselection->getCustomField();
			$selection_updated_at = $proselection->getUpdatedAt();
			$selection_product_id = $proselection->getProductId();
			  $selection_result[] = [
				 'selection_product_id' => $selection_product_id,
                 'custom_field' => $selection_custom_field,
                 'updated_at' => $selection_updated_at,
             ];
		}
		return $selection_result ;
    }


	public function getBundleProductGivenData($parent_product_id)
	{
		$selection_result = [];

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($parent_product_id);

		$selectionCollection = $product->getTypeInstance(true)
			->getSelectionsCollection(
				$product->getTypeInstance(true)->getOptionsIds($product),
				$product
			);

		foreach ($selectionCollection as $proselection) {
			$selection_result[] = [
				'selection_product_id' => $proselection->getProductId(),
				'custom_field'         => $proselection->getCustomField() ?? 0,
				'updated_at'           => $proselection->getUpdatedAt(),
				'option_id'            => $proselection->getOptionId(),
				'option_title'         => $proselection->getData('default_title'),
			];
		}

		return $selection_result;
	}

    
}
