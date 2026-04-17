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
namespace Webkul\DeliveryBoy\Api\Data;

interface OrderInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ID = "id";
    public const OTP = "otp";
    public const ORDER_ID = "order_id";
    public const INCREMENT_ID = "increment_id";
    public const ORDER_STATUS = "order_status";
    public const ASSIGN_STATUS = "assign_status";
    public const DELIVERYBOY_ID = "deliveryboy_id";
    public const ALTERNATE_DELIVERY = "alternate_delivery";
    public const PACKAGE_ITEMS = "package_items";
    public const DELIVERY_AMOUNT = "delivery_amount";
    public const COMMENTS = "comments";

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Set Id.
     *
     * @param int $id
     * @return self
     */
    public function setId($id);
    
    /**
     * Get Otp.
     *
     * @return string|null
     */
    public function getOtp();
    
    /**
     * Set Otp.
     *
     * @param string $otp
     * @return self
     */
    public function setOtp($otp);
    
    /**
     * Get ORder Id.
     *
     * @return int
     */
    public function getOrderId();
    
    /**
     * Set ORder Id.
     *
     * @param int $orderId
     * @return self
     */
    public function setOrderId($orderId);
    
    /**
     * Get INcrement ID.
     *
     * @return string|null
     */
    public function getIncrementId();
    
    /**
     * Set Increment Id.
     *
     * @param string $incrementId
     * @return self
     */
    public function setIncrementId($incrementId);
    
    /**
     * Get Order Status.
     *
     * @return string|null
     */
    public function getOrderStatus();
    
    /**
     * Set Order Status.
     *
     * @param string $orderStatus
     * @return self
     */
    public function setOrderStatus($orderStatus);
    
    /**
     * Get AssignStatus
     *
     * @return string
     */
    public function getAssignStatus();
    
    /**
     * Set AssignStatus.
     *
     * @param string $assignStatus
     * @return self
     */
    public function setAssignStatus($assignStatus);
    
    /**
     * Get DeliveryboyId
     *
     * @return int
     */
    public function getDeliveryboyId();
    
    /**
     * Set Deliveryboy Id.
     *
     * @param int $deliveryboyId
     * @return self
     */
    public function setDeliveryboyId($deliveryboyId);

    /**
     * Get Alternate Delivery
     *
     * @return string
     */
    public function getAlternateDelivery();
    
    /**
     * Set Alternate Delivery.
     *
     * @param string $alternateDelivery
     * @return self
     */
    public function setAlternateDelivery($alternateDelivery);

    /**
     * Get Package Items
     *
     * @return string
     */
    public function getPackageItems();
    
    /**
     * Set Package Items.
     *
     * @param string $packageItems
     * @return self
     */
    public function setPackageItems($packageItems);

    /**
     * Get Delivery Amount
     *
     * @return string
     */
    public function getDeliveryAmount();
    
    /**
     * Set Delivery Amount.
     *
     * @param int $deliveryAmount
     * @return self
     */
    public function setDeliveryAmount($deliveryAmount);

    /**
     * Get Comments
     *
     * @return string
     */
    public function getComments();
    
    /**
     * Set Comments.
     *
     * @param string $comments
     * @return self
     */
    public function setComments($comments);
}
