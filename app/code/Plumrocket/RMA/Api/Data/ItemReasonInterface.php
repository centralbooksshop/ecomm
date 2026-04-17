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
interface ItemReasonInterface
{
    const TITLE = 'title';
    const STATUS = 'status';
    const PAYER = 'payer';
    const POSITION = 'position';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return integer
     */
    public function getStatus(): int;

    /**
     * @return string
     */
    public function getPayer(): string;

    /**
     * @return integer
     */
    public function getPosition(): int;

    /**
     * @return string
     */
    public function getLabel(): string;
}
