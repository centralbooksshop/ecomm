<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for ubcs slide search results.
 * @api
 */
interface ItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get slide items list.
     *
     * @return \Ubertheme\UbContentSlider\Api\Data\ItemInterface[]
     */
    public function getItems();

    /**
     * Set slide items list.
     *
     * @param \Ubertheme\UbContentSlider\Api\Data\ItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
