<?php
declare(strict_types=1);

namespace Centralbooks\SchoolCode\Api\Data;

interface SchoolcodeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Schoolcode list.
     * @return \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface[]
     */
    public function getItems();

    /**
     * Set school_name list.
     * @param \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

