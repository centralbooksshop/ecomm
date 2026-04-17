<?php
 
namespace Retailinsights\Orders\Plugin;
 
use Magento\Checkout\Controller\Sidebar\UpdateItemQty as coreUpdateItemQty;
// use Vendor\Extension\Helper\Data;
use Magento\Framework\Json\Helper\Data as coreData;
use Magento\Checkout\Model\Sidebar;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Serialize\SerializerInterface;
 
 
class UpdateItemQty
{
    protected $helper;
    protected $jsonHelper;
    protected $sidebar;
    protected $quoteItemFactory;
    protected $productFactory;
    protected $cart;
    protected $serializer;
 
 
    public function __construct(
        coreData $jsonHelper,
        Sidebar $sidebar,
        Cart $cart,
        SerializerInterface $serializer,
        ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->sidebar = $sidebar;
        $this->productFactory = $productFactory;
        $this->serializer = $serializer;
        $this->cart = $cart;
        $this->_storeManager = $storeManager;
    }
 
    public function aroundExecute(coreUpdateItemQty $subject, \Closure $proceed)
    {
        if($this->getWebsiteCode() == 'schools'){
            $errorMsg= 'only 1 product permitted per class';
            return $subject->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($errorMsg))
            );  
        }
        return $proceed();
    }

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }

}