<?php 
namespace Retailinsights\Backorders\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AlwaysShipOrder implements ObserverInterface
{
protected $stockState;
protected $sourceItemsSaveInterface;
protected $sourceItemFactory;
protected $helperData;
protected $logger;

public function __construct(
   \Magento\CatalogInventory\Api\StockStateInterface $stockState,
   \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
   \Retailinsights\Backorders\Helper\Data $helperData,
	\Psr\Log\LoggerInterface $logger,
   \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory
)
{
    $this->stockState = $stockState;
    $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
    $this->sourceItemFactory = $sourceItemFactory;
    $this->helperData = $helperData;
	$this->logger = $logger;
} 

public function execute(Observer $observer)
{
    $items = $observer->getEvent()->getShipment()->getAllItems();
    $this->logger->info('Shipment');
    $order_status = '';
    foreach ($items as $item) {
         $productId =  $item->getProductId();
         if($this->helperData->isBackordred($item)){
          $order_status = 'Yes';
          $product_skus[] = $item->getSku();
          $product_qtys[] = $item->getQtyOrdered();
         }
     }
    $shipment = $observer->getEvent()->getShipment();
    $order = $shipment->getOrder();
    $orderIncrementId = $order->getIncrementId();
    $this->logger->info('Shipment ids'.json_encode($orderIncrementId));
    $order_check = explode("-",$orderIncrementId);
    $this->logger->info('Shipment ids'.json_encode($order_check));
   if(count($order_check) > 1){ 
    if($order_status =='Yes') {
     throw new LocalizedException(
                    __('Not all of your products are available in the requested quantity.')
                );
   return;
    } else {
       $order->setIsBackeorderedItems('Shipped');
       $order->save();
    }
   
   } else {
    if($order->getIsBackeorderedItems('Yes')) {
     $order->setIsBackeorderedItems('Shipped');
       $order->save();
   }
   }


     
   if($items) {
     foreach ($items as $item) {
       $productId = $item->getProductId();
       $qty = $this->stockState->getStockQty($productId);
       $itemQty = $item->getQty();
       $this->logger->info('Shipment QTY'.$item->getSku().'_'.$qty);
       $this->logger->info('Shipment TypeID_'.$item->getTypeId());

       if($item->getTypeId() == 'bundle') {
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        foreach ($options as $child) {

        $qty1 = $this->stockState->getStockQty($child->getProductId());
        $itemQty1 = $child->getQty();
        $this->logger->info('Shipment bundle QTY'.$child->getSku().'_'.$qty1);
             if ($qty1 < $itemQty1) {
         $sourceItem = $this->sourceItemFactory->create();
         $sourceItem->setSourceCode('default');
         $sourceItem->setSku($child->getSku());
         $sourceItem->setQuantity($itemQty1);
         $sourceItem->setStatus(1);
         $this->sourceItemsSaveInterface->execute([$sourceItem]);
      }
        }

       } else {

        if ($qty < $itemQty) {
         $sourceItem = $this->sourceItemFactory->create();
         $sourceItem->setSourceCode('default');
         $sourceItem->setSku($item->getSku());
         $sourceItem->setQuantity($itemQty);
         $sourceItem->setStatus(1);
         $this->sourceItemsSaveInterface->execute([$sourceItem]);
      }

       }
       
   }
}

}
}