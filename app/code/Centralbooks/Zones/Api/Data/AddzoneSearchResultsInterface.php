<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Api\Data;

interface AddzoneSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Zones list.
     * @return \Centralbooks\Zones\Api\Data\AddzoneInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     * @param \Centralbooks\Zones\Api\Data\AddzoneInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

