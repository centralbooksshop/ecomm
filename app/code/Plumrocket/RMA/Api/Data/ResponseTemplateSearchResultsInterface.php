<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @since 2.2.0
 */
interface ResponseTemplateSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Plumrocket\RMA\Api\Data\ResponseTemplateInterface[]
     */
    public function getItems();

    /**
     * @param \Plumrocket\RMA\Api\Data\ResponseTemplateInterface[] $items
     * @return self
     */
    public function setItems(array $items);
}
