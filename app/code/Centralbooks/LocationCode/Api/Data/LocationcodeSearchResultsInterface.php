<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Api\Data;

interface LocationcodeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Locationcode list.
     * @return \Centralbooks\LocationCode\Api\Data\LocationcodeInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Centralbooks\LocationCode\Api\Data\LocationcodeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

