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

interface CommentInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ID = "id";
    public const COMMENT = "comment";
    public const SENDER_ID = "sender_id";
    public const CREATED_AT = "created_at";
    public const COMMENTED_BY = "commented_by";
    public const IS_DELIVERYBOY = "is_deliveryboy";
    public const ORDER_INCREMENT_ID = "order_increment_id";
    public const DELIVERYBOY_ORDER_ID = "deliveryboy_order_id";

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param  int $id
     * @return self
     */
    public function setId($id);

    /**
     * Get Comment
     *
     * @return string
     */
    public function getComment();

    /**
     * Set Comment
     *
     * @param  string $comment
     * @return self
     */
    public function setComment($comment);

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set Created At.
     *
     * @param  string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);

    /**
     * Get SenderId
     *
     * @return int|null
     */
    public function getSenderId();

    /**
     * Set Sender Id
     *
     * @param  int $senderId
     * @return self
     */
    public function setSenderId($senderId);

    /**
     * Get isDeliveryBoy flag  0 false 1 true
     *
     * Whether the comment is written by delivery boy
     *
     * @return int|null
     */
    public function getIsDeliveryboy();

    /**
     * Set isDeliveryBoy flag  0 false 1 true
     *
     * @param  int $isDeliveryboy
     * @return self
     */
    public function setIsDeliveryboy($isDeliveryboy);

    /**
     * Get Order IncremntId
     *
     * @return int|null
     */
    public function getOrderIncrementId();

    /**
     * Set Order IncremntId.
     *
     * @param  int $orderIncrementId
     * @return self
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * Get Deliveryboy order Id.
     *
     * @return int|null
     */
    public function getDeliveryboyOrderId();

    /**
     * Set Deliveryboy ORder Id.
     *
     * @param  int $deliveryboyOrderId
     * @return self
     */
    public function setDeliveryboyOrderId($deliveryboyOrderId);
}
