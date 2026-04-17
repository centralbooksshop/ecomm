<?php
 
namespace Retailinsights\Backorders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
 
class Orderplaceafter implements ObserverInterface
{
    protected $logger;
    protected $productRepository;
    protected $helperData;
 
    public function __construct(LoggerInterface $logger,\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,\Retailinsights\Backorders\Helper\Data $helperData)
    {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		try {
			$order = $observer->getEvent()->getOrder();
			$orderItems = $order->getAllItems();

			$order_status ='';
			$back_order_data = array();
			$product_skus = array();
			$product_qtys = array();
			foreach ($orderItems as $item) {
				$productId =  $item->getProductId();
				if($this->helperData->isBackordred($item)){
					$order_status = 'Yes';
					$product_skus[] = $item->getSku();
					$product_qtys[] = $item->getQtyOrdered();
				}
			}
			if($order_status == 'Yes') {
				$back_order_data['order_id'] = $order->getIncrementId();
				$back_order_data['item_id']  = $order->getEntityId();
				$back_order_data['sku']  = implode(",", $product_skus);
				$back_order_data['qty_ordered']  = implode(",", $product_qtys);
				$back_order_data['status']  = 'New';
				$this->helperData->setBackorderData($back_order_data);
				$order->setIsBackeorderedItems($order_status); 
			}

			$orderIncrementId = $order->getIncrementId();
			$order_check = explode("-",$orderIncrementId);
			if(count($order_check) > 1){
				$this->logger->info('Its Backorder - '.$orderIncrementId);
				$order->setShippingAmount(0);
				$order->setBaseShippingAmount(0);
				$order->setGrandTotal(0);
				$order->setBaseGrandTotal(0);
			}
			$order->save();

		} catch (\Exception $e) {
			$this->logger->info($e->getMessage());
		}
    }
}