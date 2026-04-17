<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api;

use Plumrocket\RMA\Api\Data\ReturnInterface;
use Plumrocket\RMA\Api\Data\ReturnMessageInterface;
use Plumrocket\RMA\Api\Data\TrackingNumberInterface;

/**
 * @since 2.3.0
 */
interface ReturnManagementInterface
{
    /**
     * @param int $id
     * @return \Plumrocket\RMA\Api\Data\TrackingNumberSearchResultInterface
     */
    public function getTrackingNumbers(int $id);

    /**
     * @param int $id
     * @param \Plumrocket\RMA\Api\Data\TrackingNumberInterface $track
     * @return bool
     * @throws \Exception
     */
    public function addTrackingNumber(int $id, TrackingNumberInterface $track): bool;

    /**
     * @param int $id
     * @param int $trackId
     * @return bool
     */
    public function removeTrackingNumber(int $id, int $trackId): bool;

    /**
     * @param int $id
     * @return \Plumrocket\RMA\Api\Data\ReturnMessageSearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMessagesList(int $id);

    /**
     * @param int $id
     * @param \Plumrocket\RMA\Api\Data\ReturnMessageInterface $message
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addMessage(int $id, ReturnMessageInterface $message) : bool;

    /**
     * @param int $id
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function authorize(int $id): ReturnInterface;

    /**
     * @param int $id
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function receive(int $id): ReturnInterface;

    /**
     * @param int $id
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function approve(int $id): ReturnInterface;

    /**
     * @param int $id
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cancel(int $id): ReturnInterface;

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnInterface $return
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function save(ReturnInterface $return): ReturnInterface;
}
