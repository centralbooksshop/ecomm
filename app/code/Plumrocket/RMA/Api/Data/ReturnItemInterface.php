<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api\Data;

/**
 * @since 2.3.0
 */
interface ReturnItemInterface
{
    const IDENTIFIER = 'entity_id';
    const ORDER_ITEM_ID = 'order_item_id';
    const RETURN_ID = 'parent_id';
    const REASON_ID = 'reason_id';
    const CONDITION_ID = 'condition_id';
    const RESOLUTION_ID = 'resolution_id';
    const QTY_PURCHASED = 'qty_purchased';
    const QTY_REQUESTED = 'qty_requested';
    const QTY_AUTHORIZED = 'qty_authorized';
    const QTY_RECEIVED = 'qty_received';
    const QTY_APPROVED = 'qty_approved';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return ReturnItemInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderItemId(): int;

    /**
     * @param int $orderItemId
     * @return ReturnItemInterface
     */
    public function setOrderItemId(int $orderItemId): ReturnItemInterface;

    /**
     * @return int
     */
    public function getReturnId(): int;

    /**
     * @param int $returnId
     * @return ReturnItemInterface
     */
    public function setReturnId(int $returnId): ReturnItemInterface;

    /**
     * @return int
     */
    public function getReasonId(): int;

    /**
     * @param int $reasonId
     * @return ReturnItemInterface
     */
    public function setReasonId(int $reasonId): ReturnItemInterface;

    /**
     * @return int
     */
    public function getConditionId(): int;

    /**
     * @param int $conditionId
     * @return ReturnItemInterface
     */
    public function setConditionId(int $conditionId): ReturnItemInterface;

    /**
     * @return int
     */
    public function getResolutionId(): int;

    /**
     * @param int $resolutionId
     * @return ReturnItemInterface
     */
    public function setResolutionId(int $resolutionId): ReturnItemInterface;

    /**
     * @return int
     */
    public function getQtyPurchased(): int;

    /**
     * @param int $qtyPurchased
     * @return ReturnItemInterface
     */
    public function setQtyPurchased(int $qtyPurchased): ReturnItemInterface;

    /**
     * @return int
     */
    public function getQtyRequested(): int;

    /**
     * @param int $qtyRequested
     * @return ReturnItemInterface
     */
    public function setQtyRequested(int $qtyRequested): ReturnItemInterface;

    /**
     * @return int|null
     */
    public function getQtyAuthorized(): ?int;

    /**
     * @param int $qtyAuthorized
     * @return ReturnItemInterface
     */
    public function setQtyAuthorized(int $qtyAuthorized): ReturnItemInterface;

    /**
     * @return int|null
     */
    public function getQtyReceived(): ?int;

    /**
     * @param null|int $qtyReceived
     * @return ReturnItemInterface
     */
    public function setQtyReceived($qtyReceived): ReturnItemInterface;

    /**
     * @return int|null
     */
    public function getQtyApproved(): ?int;

    /**
     * @param null|int $qtyApproved
     * @return ReturnItemInterface
     */
    public function setQtyApproved($qtyApproved): ReturnItemInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return ReturnItemInterface
     */
    public function setCreatedAt(string $createdAt): ReturnItemInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return ReturnItemInterface
     */
    public function setUpdatedAt(string $updatedAt): ReturnItemInterface;
}
