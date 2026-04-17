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
interface TrackingNumberSearchResultInterface
{
    /**
     * @return \Plumrocket\RMA\Api\Data\TrackingNumberInterface[]
     */
    public function getItems();

    /**
     * @param \Plumrocket\RMA\Api\Data\TrackingNumberInterface[] $items
     * @return self
     */
    public function setItems(array $items);
}
