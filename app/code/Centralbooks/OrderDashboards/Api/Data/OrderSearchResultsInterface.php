<?php
declare(strict_types=1);

namespace Centralbooks\OrderDashboards\Api\Data;

interface OrderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Order list.
     * @return \Centralbooks\OrderDashboards\Api\Data\OrderInterface[]
     */
    public function getItems();

    /**
     * Set EntityId list.
     * @param \Centralbooks\OrderDashboards\Api\Data\OrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

