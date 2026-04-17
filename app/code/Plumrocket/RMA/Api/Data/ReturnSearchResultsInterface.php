<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api\Data;

/**
 * @since 2.2.0
 */
interface ReturnSearchResultsInterface
{
    /**
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface[]
     */
    public function getItems();

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnInterface[] $items
     * @return self
     */
    public function setItems(array $items);
}
