<?php

namespace Retailinsights\WalkinCustomers\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductCategoryList;

 
 
class StatusChangeWalkinCustomers implements ObserverInterface
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
    protected $_scopeConfig;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductCategoryList $productCategory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->productCategory = $productCategory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderIds[0]);

        $method = $order->getPayment()->getMethod();
        if( ($method == 'receivedpaymentcash') || ($method == 'receivedpaymentcard')){
            $this->changeOrderStatus($order);
            // $this->generateInvoice($order);
        }
    }
    
    function changeOrderStatus($order){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/receivedpaymentcash/orderStatus');
            $state = $order->getState();
            $status = $config;
            $comment = '';
            $isNotified = false;
            $order->setState($state);
            $order->setStatus($status);
            $order->addStatusToHistory($order->getStatus(), $comment);
            $order->save(); 
    }

    function generateInvoice($order){
        $orderId = $order->getId(); //order id for which want to create invoice
        
        $order = $this->_orderRepository->get($orderId);
        if($order->canInvoice()) {
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->_transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            // $this->invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->save();
        }
    }
    
}