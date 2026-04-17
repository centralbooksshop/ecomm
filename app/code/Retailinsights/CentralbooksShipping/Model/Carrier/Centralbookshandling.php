<?php

namespace Retailinsights\CentralbooksShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Catalog\Model\ProductCategoryList;

/**
 * Custom shipping model
 */
class Centralbookshandling extends AbstractCarrier implements CarrierInterface
{
    const WEBSITE_CODE_BASE = 'base';
    const WEBSITE_CODE_SCHOOL = 'school';
    protected $_code = 'centralbookshandling';
    protected $_scopeConfig;
    protected $_storeScope =  \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    protected $_storeManager;
    protected $_checkoutSession;
    protected $productCategory;
    private $rateResultFactory;
    private $rateMethodFactory;
    private $_schoolData;
    private $collectionFactory;
    protected $pincodeCollection;
    protected $schoolConfig;
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Lof\PincodeChecker\Model\ResourceModel\Pincodechecker\CollectionFactory $pincodeCollection,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        ProductCategoryList $productCategory,
        \SchoolZone\Addschool\Helper\Data $schoolData,
	\SchoolZone\Addschool\Model\SimilarproductsattributesFactory $collectionFactory,
	\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolConfig,
	\Retailinsights\CentralbooksShipping\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->_scopeConfig = $scopeConfig;
	$this->pincodeCollection = $pincodeCollection;
        $this->_checkoutSession = $checkoutSession;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->_storeManager = $storeManager;
        $this->logger=$logger;
        $this->productCategory = $productCategory;
        $this->_schoolData = $schoolData;
	$this->collectionFactory = $collectionFactory;
	$this->schoolConfig = $schoolConfig;
	$this->helper = $helper;
    }

    public function collectRates(RateRequest $request)
    {

	if (!$this->getConfigFlag('active')) {
            return false;
	}

	$result = $this->rateResultFactory->create();
	$schoolName = $this->helper->getProductSchoolName();
	$hybridDeliveryStatus = $this->helper->getHybridModeStatus($schoolName);
	$currentWebsiteCode = $this->getCurrentWebsiteCode();
	$shippingBaseCost = (float)$this->getConfigData('shipping_cost');
	if($hybridDeliveryStatus == 1){
	        $hybrid_product_handling_fee = '';
		if ($request->getFreeShipping() === true) {
	             $finalPrice = 0;
        	}else {
	        	if($currentWebsiteCode == 'schools'){
		                $shipping_amt = $this->_checkoutSession->getSplitShipAmount();
	        	        if(isset($shipping_amt)) {
                		   $finalPrice = $this->_checkoutSession->getSplitShipAmount();
				} else {
					$this->logger->info(" rateData handling fee : ");
					$rateData = $this->helper->getAdditionalHybridRateForSchoolWebsite();
					$hybrid_product_handling_fee = $rateData['hybrid_product_handling_fee'];
					$this->logger->info(" rateData handling fee : ". print_r($rateData, true));
        	        	}
	                }else {
        	           $finalPrice = $this->getAdditionalRate();
                	}
		}

   	        $methodHandling = $this->rateMethodFactory->create();
                $methodHandling->setCarrier($this->_code);
                $methodHandling->setCarrierTitle('School Delivery');
                $method_handling_title = $this->getConfigData('handlingname');
                $methodHandling->setMethod($this->_code);
		$methodHandling->setMethodTitle($method_handling_title);
		if($hybrid_product_handling_fee >= 0){
			$methodHandling->setPrice($hybrid_product_handling_fee);
		}else{
			$methodHandling->setPrice($finalPrice);
		}
		$methodHandling->setCost($shippingBaseCost);
		$result->append($methodHandling);
	}
        return $result;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    public function getAdditionalRate()
    {
      $shippingBaseCost = (float)$this->getConfigData('shipping_cost');
      $stepRate = (float)$this->getConfigData('step_shipping_cost');;
      $stepWeight = $this->getConfigData('step_shipping_weight');
      if($stepRate!='' && $stepWeight!=''){
            $productWeight = $this->getProductWeight();
            $productWeight = $productWeight * 1000;
            $slabRatio = floor($productWeight / $stepWeight);
            $addRate = $slabRatio * $stepRate;
            $finalRate = $shippingBaseCost + $addRate;
      }else{
            $finalRate = $shippingBaseCost;
      }
      return $finalRate;
    }

    public function getCurrentWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }

    public function getCollection()
    {
        return $this->pincodeCollection->create();
    }

    public function getItemPincodePrice($postcode) 
    {
	$pincodecollection = $this->getCollection();
        $pincodecollection->addFieldToFilter('pincode', array('eq' => $postcode));
	if($pincodecollection->getData()){
	    $pincodecoll =  $pincodecollection->getFirstItem()->getData();
            foreach ($pincodecoll as $key => $pincodevalue) {  
		if($key == 'pincode_price'){
			$customprice = $pincodevalue;
			break;
		}
	    }
	    $count = $this->CartItemCount();
            $customfinalprice = ((float)$customprice * (float)$count);
        }else {
	    $customfinalprice = '';
	}
        return $customfinalprice;
    }

    public function getAdditionalRateForSchoolWebsite()
    {
        $finalPrice = '';
	$product_shipping_charges = $this->CustomShippingRate();
        $product_handling_fee = $this->CustomHandlingRate();
	if($product_handling_fee > 0 && $product_handling_fee != '') {
		$rate = $product_handling_fee + $product_shipping_charges;
	} elseif($product_shipping_charges >= 0 || $product_shipping_charges != ''){
		$rate = $product_shipping_charges;
	} else {
		$shippingCost = (float)$this->getConfigData('flat_shipping_cost');
		$shippingCostPerKG = $this->getConfigData('shipping_cost_per_kg');
		$thresholdWeight = $this->getConfigData('threshold_weight');
		$productWeight = $this->getProductWeight();
		if(!empty($thresholdWeight) && $productWeight >= $thresholdWeight){
			$extraWeight = $productWeight - $thresholdWeight;
			$extraRate = $extraWeight * $shippingCostPerKG;
			$rate = $shippingCost + $extraRate;
		}else{
			$count = $this->CartItemCount();
			$rate = (float)$shippingCost * (float)$count;
		}
	}
	if($product_handling_fee == 0 || $product_handling_fee == '') {
  	       $schoolName = $this->getProductSchoolName();	
               $schoolCollection = $this->schoolConfig->create()->addFieldToSelect('*')
	   			  ->addFieldToFilter('school_name_text', $schoolName);
	       $schoolDeliveryStatus = $schoolCollection->getFirstItem()->getData('school_delivery');
	       if($schoolDeliveryStatus == 0){
			$postCode = $this->_checkoutSession->getQuote()->getShippingAddress()->getPostcode();
			$itemPincodePrice = $this->getItemPincodePrice($postCode);
			if(!empty($itemPincodePrice)) {
				$finalPrice  = $rate + $itemPincodePrice;
			} else {
				$finalPrice  = $rate;
			} 
	        }else{
		    	$finalPrice = 0;
		}  
	} else {
		$finalPrice  = $rate;
	}
        return $finalPrice;
    }

    public function CustomShippingRate()
    {
        try {
			$shippingcharge = 0;
			$allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
			$allItemsCount = count($allItems);
			if($allItemsCount > 0) {
				$product_id = '';
				foreach($allItems as $item) {
					$product_type = $item->getProductType();
					if($product_type == 'bundle') {
					   $product_id = $item->getProductId();
					}
				    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				    $productval = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
   				    $handling_chargesval = $productval->getData('product_handling_fee');
		                    if($handling_chargesval == 0) {
					    $shipping_chargesval = $productval->getData('product_shipping_charges');
			   		    $shippingcharge += $shipping_chargesval;
				     }
				}
			}
	} catch(\Exception $e) {
            $this->logger->error($e->getMessage());
        }
	return ($shippingcharge);
    }

    public function CustomHandlingRate()
    {    
        try {
			$handling_charge = 0;
			$allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
			$allItemsCount = count($allItems);
            if($allItemsCount > 0) {
				$product_id = '';
				foreach($allItems as $item) {
					$product_type = $item->getProductType();
					if($product_type == 'bundle') {
					   $product_id = $item->getProductId();
					}

					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				    $productval = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
					$handling_chargesval = $productval->getData('product_handling_fee');
				    $handling_charge += $handling_chargesval;
				}
			
			}
                    
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return ($handling_charge);
    }

    public function getPriceByProduct(int $productId)
    {
        $price = 0;
        $category = $this->getCategoryIds($productId);
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryFactory->create()                              
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id',['eq'=>$category[count($category)-1]])
                    ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
                  
        }catch(\Exception $e){
            $this->logger->error($e->getMessage());
        }
        $data = $categories->getFirstItem();
        $catData = $data->getData();
        foreach ($catData as $key => $value) {  
            if($key == 'shipping_charges'){
                $price = $value;
            }
        }
        return $price;
    }

    public function categoryShippingRate()
    {
        $productId = array();
        $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($cartData as $key => $value) {
            $product = $value->getProduct();
            $productId[]=$product->getId();
        }
        $catPrice = 0;
        foreach($productId as $key => $value){
            $prevCatPrice = $this->getPriceByCategory($value);
            $catPrice = $catPrice + $prevCatPrice;
        }
        return $catPrice;
    }

    public function getPriceByCategory(int $productId)
    {
        $price = 0;
        $category = $this->getCategoryIds($productId);
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryFactory->create()                              
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id',['eq'=>$category[count($category)-1]])
                    ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
                  
        }catch(\Exception $e){
            $this->logger->error($e->getMessage());
        }
        $data = $categories->getFirstItem();
        $catData = $data->getData();
        foreach ($catData as $key => $value) {  
            if($key == 'shipping_charges'){
                $price = $value;
            }
        }
        return $price;
    }

    public function getCategoryIds(int $productId)
    {
        $categoryIds = $this->productCategory->getCategoryIds($productId);
        $category = [];
        if ($categoryIds) {
            $category = array_unique($categoryIds);
        }
        return $category;
    }

    public function getProductWeight()
    {
        $weight = 0;
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
            $items = $cart->getQuote()->getAllItems();
            $weight = 0;
            foreach($items as $item) {
                $weight += ($item->getWeight() * $item->getQty()) ;        
            }
        }catch(\Exception $e){
            $this->logger->error($e->getMessage());
        }
        // returns in kg
        return $weight;
    }

    public function CartItemCount(){
        $cartDataCount = 0;
        $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
		$cartDataCount = count($cartData);
        return $cartDataCount;
    }

}

