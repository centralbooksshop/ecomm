<?php

namespace Retailinsights\Orders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;

 
 
class StatusChangeinvoiceGenerate implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * 
     * 
     */

    protected $productRepository;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_SESSION["optional_items"]=''; //removing optional items on invoice session
        $orderIds = $observer->getEvent()->getOrderIds();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderIds[0]);

        try {
            if(!$order->canInvoice()) {
                return null;
            }
            if(!$order->getState() == 'new') {
                return null;
            }
            if(($order->getPayment()->getMethodInstance()->getCode() == 'ccavenue') ||
                ($order->getPayment()->getMethodInstance()->getCode() == 'checkmo') ||
                ($order->getStatus() == 'canceled') || ($order->getStatus() == 'pending')){
                    return null; 
            }
            if(($order->getState() == 'new') && ($order->getStatus() == 'processing')) {
               // $this->generateInvoice($order);
            }

        } catch (\Exception $e) {
            $order->addStatusHistoryComment('Exception message: '.$e->getMessage(), false);
            $order->save();
            return null;
        }
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