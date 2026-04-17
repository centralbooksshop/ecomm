<?php 
namespace Retailinsights\SplitOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AlwaysShipOrder implements ObserverInterface
{
protected $stockState;
protected $sourceItemsSaveInterface;
protected $sourceItemFactory;

public function __construct(
   \Magento\CatalogInventory\Api\StockStateInterface $stockState,
   \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
   \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory
)
{
    $this->stockState = $stockState;
    $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
    $this->sourceItemFactory = $sourceItemFactory;
} 

public function execute(Observer $observer)
{
   $items = $observer->getEvent()->getShipment()->getAllItems();
    //$logger->info('Shipment');
   if($items) {
     foreach ($items as $item) {
       $productId = $item->getProductId();
       $qty = $this->stockState->getStockQty($productId);
       $itemQty = $item->getQty();
       //$logger->info('Shipment QTY'.$item->getSku().'_'.$qty);
       //$logger->info('Shipment TypeID_'.$item->getTypeId());

       if($item->getTypeId() == 'bundle') {
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        foreach ($options as $child) {

           $qty1 = $this->stockState->getStockQty($child->getProductId());
       $itemQty1 = $child->getQty();
        //$logger->info('Shipment bundle QTY'.$child->getSku().'_'.$qty1);
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