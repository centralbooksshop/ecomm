<?php

namespace Retailinsights\CentralbooksShipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{

	protected $_checkoutSession;
	protected $schoolConfig;
	protected $pincodeCollection;

        public function __construct(
	\Magento\Checkout\Model\Session $checkoutSession,
	\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolConfig,
	\Lof\PincodeChecker\Model\ResourceModel\Pincodechecker\CollectionFactory $pincodeCollection,
	\Psr\Log\LoggerInterface $logger,
        Context $context
    )
    {
	    $this->_checkoutSession = $checkoutSession;
	    $this->schoolConfig = $schoolConfig;
	    $this->pincodeCollection = $pincodeCollection;
	    $this->logger=$logger;
	    parent::__construct($context);
    }

    public function getProductSchoolName()
    {
        $schoolName = '';
		try {
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
						$schoolOptionId = $productval->getData('school_name');
						$eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
						$attribute = $eavConfig->getAttribute('catalog_product', 'school_name');
						$schoolName = $attribute->getSource()->getOptionText($schoolOptionId);
					}
				}
            } catch(\Exception $e) {
                    $this->logger->error($e->getMessage());
	        }
          return $schoolName;
    }    

    public function getHybridModeStatus($schoolName)
    {
	$schoolCollection = $this->schoolConfig->create()->addFieldToSelect('*')
                                 ->addFieldToFilter('school_name_text', $schoolName);
        $hybridDeliveryStatus = $schoolCollection->getFirstItem()->getData('hybrid_delivery');
	return $hybridDeliveryStatus;
    }

     public function HybridShippingRate()
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
                                    $shipping_chargesval = $productval->getData('hybrid_shipping_charges');
                                    $shippingcharge += $shipping_chargesval;
                                }
                        }
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return ($shippingcharge);
    }


    public function hybridHandlingRate()
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
                                        $handling_chargesval = $productval->getData('hybrid_product_handling_fee');
                                        $handling_charge += $handling_chargesval;
                                }
                        }

        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return ($handling_charge);
    }


   public function getAdditionalHybridRateForSchoolWebsite()
   {
    $finalPrice = 0;
    $productHF = 0;
    $productSC = 0;

    $product_shipping_charges = $this->HybridShippingRate();
    $product_handling_fee     = $this->hybridHandlingRate();

    if (($product_handling_fee >= 0 && $product_handling_fee !== '') &&
        ($product_shipping_charges >= 0 || $product_shipping_charges !== '')) {

        $productHF = $product_handling_fee;
        $productSC = $product_shipping_charges;

        if ($productSC >= 0 && $productSC !== '') {
            $schoolName = $this->getProductSchoolName();
            $schoolCollection = $this->schoolConfig->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('school_name_text', $schoolName);
	    $schoolDeliveryStatus = $schoolCollection->getFirstItem()->getData('school_delivery');
            if ($schoolDeliveryStatus == 0) {
                $postCode = $this->_checkoutSession->getQuote()->getShippingAddress()->getPostcode();
                $itemPincodePrice = $this->getItemPincodePrice($postCode);

                if (!empty($itemPincodePrice)) {
                    $finalPrice = $productSC + $itemPincodePrice;
                } else {
                    $finalPrice = $productSC;
                }
            } else {
                $finalPrice = 0;
            }
        }
    }

    return [
        'hybrid_product_shipping_fee' => $finalPrice,
        'hybrid_product_handling_fee' => $productHF
    ];
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

    public function getCollection()
    {
        return $this->pincodeCollection->create();
    }

    public function CartItemCount(){
        $cartDataCount = 0;
        $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
                $cartDataCount = count($cartData);
        return $cartDataCount;
    }



 }
