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

interface OrderTransactionInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ID = "entity_id";
    public const DELIVERYBOY_ORDER_ID = "deliveryboy_order_id";
    public const AMOUNT = "amount";
    public const TRANSACTION_ID = "transaction_id";
    public const IS_CLOSED = "is_closed";
    public const CREATED_AT = "created_at";
    public const UPDATED_AT = "updated_at";

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
     * Get deliveryboyOrderId
     *
     * @return string|null
     */
    public function getDeliveryboyOrderId();

    /**
     * Set deliveryboyOrderId
     *
     * @param string $deliveryboyOrderId
     * @return self
     */
    public function setDeliveryboyOrderId($deliveryboyOrderId);

    /**
     * Get Acmount
     *
     * @return string|null
     */
    public function getAmount();

    /**
     * Set Amount
     *
     * @param string $amount
     * @return self
     */
    public function setAmount($amount);

    /**
     * Get TransactionId
     *
     * @return float|null
     */
    public function getTransactionId();

    /**
     * Set TransactionId
     *
     * @param float $transactionId
     * @return self
     */
    public function setTransactionId($transactionId);

    /**
     * Get Is cloded.
     *
     * @return float|null
     */
    public function getIsClosed();

    /**
     * Set Is Closed.
     *
     * @param float $isClosed
     * @return self
     */
    public function setIsClosed($isClosed);

    /**
     * Get Created at.
     *
     * @return float|null
     */
    public function getCreatedAt();

    /**
     * Set Created At.
     *
     * @param float $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);
    /**
     * Get Updated at.
     *
     * @return float|null
     */
    public function getUpdatedAt();

    /**
     * Set uPdated at.
     *
     * @param float $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt);
}
