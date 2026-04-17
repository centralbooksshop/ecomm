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

class SetOrderCoordinates implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    
    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    private $deliveryboyHelper;
    
    /**
     * @var \Webkul\DeliveryBoy\Helper\DeliveryAutomation
     */
    private $deliveryAutomationHelper;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\OrderLocation\CollectionFactory
     */
    private $orderLocCollF;
    
    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Webkul\DeliveryBoy\Helper\DeliveryAutomation $deliveryAutomationHelper
     * @param LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\OrderLocation\CollectionFactory $orderLocCollF
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Webkul\DeliveryBoy\Helper\DeliveryAutomation $deliveryAutomationHelper,
        LoggerInterface $logger,
        \Webkul\DeliveryBoy\Model\ResourceModel\OrderLocation\CollectionFactory $orderLocCollF
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->deliveryAutomationHelper = $deliveryAutomationHelper;
        $this->logger = $logger;
        $this->orderLocCollF = $orderLocCollF;
    }

    /**
     * Set customer address coordinates.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            $orderId = $order->getId();
            $orderLocation = $this->orderLocCollF->create()
                ->addFieldToFilter('order_id', $orderId)
                ->getFirstItem();
            $deliveryAddress = $this->getDeliveryAddress($order);
            $orderCoord = $this->deliveryAutomationHelper
                ->getAddressCoordinates($deliveryAddress);
            $this->logger->debug(json_encode($orderCoord));

            if (is_array($orderCoord)) {
                $orderLocation->setLatitude($orderCoord['latitude'])
                    ->setLongitude($orderCoord['longitude'])
                    ->setOrderId($orderId)
                    ->save();
            }
            
        } catch (Throwable $e) {
            $this->logger->debug(__CLASS__);
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Is address Exists.
     *
     * @param Address $address
     * @return bool
     */
    public function isAddressEmpty($address)
    {
        return $address || empty($address->getData());
    }

    /**
     * Extract Address from order.
     *
     * @param Order $order
     * @return Address
     */
    public function getDeliveryAddress($order)
    {
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        return $this->isAddressEmpty($shippingAddress) ? $billingAddress : $shippingAddress;
    }
}
