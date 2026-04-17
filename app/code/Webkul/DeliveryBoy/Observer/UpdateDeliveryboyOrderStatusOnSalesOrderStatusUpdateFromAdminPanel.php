<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Observer;

use Psr\Log\LoggerInterface;
use Throwable;

class UpdateDeliveryboyOrderStatusOnSalesOrderStatusUpdateFromAdminPanel implements
    \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    private $deliveryboyOrderCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->deliveryboyOrderCollectionFactory = $deliveryboyOrderCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Change deliveryboy order status on order status change from admin panel.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
		$order = $this->getOrderFromObserver($observer);
//		$this->logger->info("justin : ". $order-getId());
            $deliveryboyOrderCollection = $this->getDeliveryboyOrderCollection($order);
            $this->syncDeliveryboyOrderStatus($deliveryboyOrderCollection, $order, $observer);
        } catch (Throwable $e) {
            $this->logger->debug(__CLASS__);
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Sync Deliveryboy Order status.
     *
     * @param Deliveryboy/Order $deliveryboyOrderCollection
     * @param Sales/Order $order
     * @param ObserverInterface $observer
     * @return void
     */
    public function syncDeliveryboyOrderStatus($deliveryboyOrderCollection, $order, $observer)
    {
        foreach ($deliveryboyOrderCollection as $deliveryboyOrder) {
            $deliveryboyOrder->setOrderStatus($this->getOrderStatusToChange($order, $observer))->save();
        }
    }

    /**
     * Get correct Status of deliveryboy order.
     *
     * @param Sales/Order $order
     * @param ObserverInterface $observer
     * @return void
     */
    public function getOrderStatusToChange($order, $observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'sales_order_payment_refund':
                return $order->getStatus();
            default:
                return $order->getStatus(); //$order->getState();
        }
    }

    /**
     * Get Deliveryboy order collection.
     *
     * @param Sales/Order $order
     * @return Deliveryboy/Order/Collection
     */
    public function getDeliveryboyOrderCollection($order)
    {
        return $this->deliveryboyOrderCollectionFactory->create()
        ->addFieldToFilter(
            "increment_id",
            $order->getIncrementId()
        );
    }

    /**
     * Get order from Observer based on event type.
     *
     * @param ObserverInterface $observer
     * @return Order
     */
    public function getOrderFromObserver($observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'sales_order_payment_refund':
                return $observer->getCreditmemo()->getOrder();
            default:
                return $observer->getOrder();
        }
    }
}
