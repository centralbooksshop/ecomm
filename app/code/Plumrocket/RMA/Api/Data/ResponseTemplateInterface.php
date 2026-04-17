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
interface ResponseTemplateInterface
{
    const TITLE = 'title';
    const MESSAGE = 'message';

    /**
     * Retrieve name of response template
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Retrieve HTML message
     *
     * @return string
     */
    public function getMessage(): string;
}
