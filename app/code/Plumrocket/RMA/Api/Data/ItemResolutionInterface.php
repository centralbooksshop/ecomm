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
interface ItemResolutionInterface
{
    const TITLE = 'title';
    const STATUS = 'status';
    const POSITION = 'position';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @return string
     */
    public function getLabel(): string;
}
