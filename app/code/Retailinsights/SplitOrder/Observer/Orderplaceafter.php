<?php
 
namespace Retailinsights\SplitOrder\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
 
class Orderplaceafter implements ObserverInterface
{
    protected $logger;
    protected $productRepository;
    protected $helperData;
 
    public function __construct(LoggerInterface $logger,\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,\Retailinsights\SplitOrder\Helper\Data $helperData)
    {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        try {

            $order = $observer->getEvent()->getOrder();
            $this->logger->info('order:'.$order->getIncrementId());
   //          $orderItems = $order->getAllItems();
        
   //   $order_status ='';
   //   $back_order_data = array();
   //   $product_skus = array();
   //   $product_qtys = array();
   //  foreach ($orderItems as $item) {
   //       $productId =  $item->getProductId();
   //       if($this->helperData->isBackordred($item)){
   //        $order_status = 'Yes';
   //        $product_skus[] = $item->getSku();
   //        $product_qtys[] = $item->getQtyOrdered();
   //       }
   //   }
      
   //    $orderIncrementId = $order->getIncrementId();
   //          $order_check = explode("-",$orderIncrementId);
   //      if(count($order_check) > 1){
   //  $this->logger->info('Its Backorder - '.$orderIncrementId);
   //  $order->setShippingAmount(0);
   // $order->setBaseShippingAmount(0);
   // $order->setGrandTotal(0);
   // $order->setBaseGrandTotal(0);
   //      }
   //          $order->save();

        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}