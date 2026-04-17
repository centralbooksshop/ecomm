<?php

namespace Retailinsights\WalkinCustomers\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductCategoryList;

 
 
class PaymentMethodAvailable implements ObserverInterface
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
    protected $productRepository;
    protected $registry;
    protected $_storeManager;
    protected $productCategory;
    private $logger;
    private $customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductCategoryList $productCategory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->productCategory = $productCategory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $customerGroupId = $this->customerSession->getCustomer()->getGroupId();
        $methodCode = $observer->getEvent()->getMethodInstance()->getCode();
        
        if($customerGroupId != '2'){
            if($methodCode=="receivedpaymentcard"){
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
            if($methodCode=="receivedpaymentcash"){
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        }
        if($customerGroupId == '2'){
            if($methodCode=="receivedpaymentcard"){
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', true);
            }
            if($methodCode=="receivedpaymentcash"){
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', true);
            }
        }
    }
    
}