<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Api\Data;

interface ZonesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Zones list.
     * @return \Centralbooks\Zones\Api\Data\ZonesInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     * @param \Centralbooks\Zones\Api\Data\ZonesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

