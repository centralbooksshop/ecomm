<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api\Data;

/**
 * @since 2.2.0
 */
interface ReturnInterface
{
    const INCREMENT_ID = 'increment_id';
    const IDENTIFIER = 'entity_id';
    const ORDER_ID = 'order_id';
    const MANAGER_ID = 'manager_id';
    const IS_CLOSED = 'is_closed';
    const STATUS = 'status';
    const SHIPPING_LABEL = 'shipping_label';
    const NOTE = 'note';
    const CODE = 'code';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_ID = 'customer_id';

    /**
     * @return string
     */
    public function getIncrementId(): string;

    /**
     * @return int
     */
    public function getIdentifier(): int;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @return int
     */
    public function getManagerId(): int;

    /**
     * @return bool
     */
    public function getIsClosed(): bool;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return string
     */
    public function getShippingLabel(): string;

    /**
     * @return string
     */
    public function getNote(): string;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @return \Plumrocket\RMA\Api\Data\ReturnItemInterface[]
     */
    public function getItems();

    /**
     * @return string
     */
    public function getCustomerEmail(): string;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @return \Plumrocket\RMA\Api\Data\ReturnMessageInterface[]
     */
    public function getMessages();

    /**
     * @return \Plumrocket\RMA\Api\Data\TrackingNumberInterface[]
     */
    public function getTrackingNumbers(): array;

    /**
     * @param string $incrementId
     * @return ReturnInterface
     */
    public function setIncrementId(string $incrementId): ReturnInterface;

    /**
     * @param int $id
     * @return ReturnInterface
     */
    public function setIdentifier(int $id): ReturnInterface;

    /**
     * @param int $id
     * @return ReturnInterface
     */
    public function setOrderId(int $id): ReturnInterface;

    /**
     * @param int $id
     * @return ReturnInterface
     */
    public function setManagerId(int $id): ReturnInterface;

    /**
     * @param bool $flag
     * @return ReturnInterface
     */
    public function setIsClosed(bool $flag): ReturnInterface;

    /**
     * @param string $status
     * @return ReturnInterface
     */
    public function setStatus(string $status): ReturnInterface;

    /**
     * @param string $label
     * @return ReturnInterface
     */
    public function setShippingLabel(string $label): ReturnInterface;

    /**
     * @param string $note
     * @return ReturnInterface
     */
    public function setNote(string $note): ReturnInterface;

    /**
     * @param string $code
     * @return ReturnInterface
     */
    public function setCode(string $code): ReturnInterface;

    /**
     * @param string $date
     * @return ReturnInterface
     */
    public function setCreatedAt(string $date): ReturnInterface;

    /**
     * @param string $date
     * @return ReturnInterface
     */
    public function setUpdatedAt(string $date): ReturnInterface;

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnItemInterface[] $items
     * @return ReturnInterface
     */
    public function setItems($items): ReturnInterface;
}
