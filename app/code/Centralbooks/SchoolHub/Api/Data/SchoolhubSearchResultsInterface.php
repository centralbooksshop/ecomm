<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Api\Data;

interface SchoolhubSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Schoolhub list.
     * @return \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

