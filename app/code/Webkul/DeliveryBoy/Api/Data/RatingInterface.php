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

interface RatingInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ID = "id";
    public const TITLE = "title";
    public const RATING = "rating";
    public const STATUS = "status";
    public const COMMENT = "comment";
    public const CREATED_AT = "created_at";
    public const CUSTOMER_ID = "customer_id";
    public const DELIVERYBOY_ID = "deliveryboy_id";

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     * @return self
     */
    public function setId($id);

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title);

    /**
     * Get Status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status);

    /**
     * Get Rating
     *
     * @return float|null
     */
    public function getRating();

    /**
     * Set Rating
     *
     * @param float $rating
     * @return self
     */
    public function setRating($rating);

    /**
     * Get Comment
     *
     * @return string|null
     */
    public function getComment();

    /**
     * Set Comment.
     *
     * @param string $comment
     * @return self
     */
    public function setComment($comment);

    /**
     * Get Created at.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set Created at.
     *
     * @param string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Customer Id.
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     * @return self
     */
    public function setCustomerId($customerId);

    /**
     * Get Deliveryboy Id.
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
}
