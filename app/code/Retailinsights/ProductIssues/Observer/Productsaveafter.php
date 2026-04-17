<?php

namespace Retailinsights\ProductIssues\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsaveafter implements ObserverInterface { 
	
    protected $_catalogSession;
    protected $_customerSession;
    protected $_checkoutSession;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {        
        $this->catalogSession = $catalogSession;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }
			public function execute(\Magento\Framework\Event\Observer $observer) {
				$_product = $observer->getProduct();  // you will get product object
				$_sku=$_product->getSku(); // for sku
				//$this->catalogSession->setMyValue('dublicated');
				$dublicated = $this->catalogSession->getMyValue();
				//echo '<pre>'; print_r($dublicated); die;

				if(!isset($dublicated)) {
					if($_product->getIsDuplicate() && ($_product->getStatus() == 2 || $_product->getStatus() == 1)) {	
						$_product->setIsInStock(true);
						$_product->setIsDuplicate(false);
						$_product->setStatus(1);
						$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						$stokRegistery = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
						if($_product->getTypeId() != 'simple') {
						    $_product->setQuantityAndStockStatus(['qty' => 100, 'is_in_stock' => (bool)100]);
						} else {
							   $qty =  $stokRegistery->getStockItemBySku($_product->getSku())->getQty();
							if($qty > 0) {
							    $_product->setQuantityAndStockStatus(['qty' => $qty, 'is_in_stock' => (bool)$qty]);
							} else {
							    $_product->setQuantityAndStockStatus(['qty' => 5, 'is_in_stock' => (bool)5]);
							}
						}
						//$_product->save();
					}
				} else {

				//$this->catalogSession->setMyValue('dublicated');
				$this->catalogSession->unsMyValue('dublicated');
				//unset($_SESSION['dublicated']);	
				}
            }   
}
