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
interface ReturnMessageSearchResultInterface
{
    /**
     * @return \Plumrocket\RMA\Api\Data\ReturnMessageInterface[]
     */
    public function getItems();

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnMessageInterface[] $items
     * @return self
     */
    public function setItems(array $items);
}
