<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns;

use Plumrocket\RMA\Api\Data\TrackingNumberInterface;
use Magento\Framework\Model\AbstractModel;

class Track extends AbstractModel implements TrackingNumberInterface
{
    /**
     * Track from customer
     */
    const FROM_CUSTOMER = 'customer';

    /**
     * Track from manager (admin)
     */
    const FROM_MANAGER = 'manager';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\ResourceModel\Returns\Track');
    }

    /**
     * @inheritDoc
     */
    public function getReturnId(): int
    {
        return (int) $this->getData(self::RETURN_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReturnId(int $returnId): TrackingNumberInterface
    {
        $this->setData(self::RETURN_ID, $returnId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return (string) $this->getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): TrackingNumberInterface
    {
        $this->setData(self::TYPE, $type);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCarrierCode(): string
    {
        return (string) $this->getData(self::CARRIER_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCarrierCode(string $carrierCode): TrackingNumberInterface
    {
        $this->setData(self::CARRIER_CODE, $carrierCode);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTrackNumber(): string
    {
        return (string) $this->getData(self::TRACK_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setTrackNumber(string $trackNumber): TrackingNumberInterface
    {
        $this->setData(self::TRACK_NUMBER, $trackNumber);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): TrackingNumberInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): TrackingNumberInterface
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }
}
