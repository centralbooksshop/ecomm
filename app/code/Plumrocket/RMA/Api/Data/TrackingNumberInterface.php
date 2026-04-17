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
interface TrackingNumberInterface
{
    const IDENTIFIER = 'entity_id';
    const RETURN_ID = 'parent_id';
    const TYPE = 'type';
    const CARRIER_CODE = 'carrier_code';
    const TRACK_NUMBER = 'track_number';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return TrackingNumberInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getReturnId(): int;

    /**
     * @param int $returnId
     * @return TrackingNumberInterface
     */
    public function setReturnId(int $returnId): TrackingNumberInterface;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     * @return TrackingNumberInterface
     */
    public function setType(string $type): TrackingNumberInterface;


    /**
     * @return string
     */
    public function getCarrierCode(): string;

    /**
     * @param string $carrierCode
     * @return TrackingNumberInterface
     */
    public function setCarrierCode(string $carrierCode): TrackingNumberInterface;

    /**
     * @return string
     */
    public function getTrackNumber(): string;

    /**
     * @param string $trackNumber
     * @return TrackingNumberInterface
     */
    public function setTrackNumber(string $trackNumber): TrackingNumberInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return TrackingNumberInterface
     */
    public function setCreatedAt(string $createdAt): TrackingNumberInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return TrackingNumberInterface
     */
    public function setUpdatedAt(string $updatedAt): TrackingNumberInterface;
}
