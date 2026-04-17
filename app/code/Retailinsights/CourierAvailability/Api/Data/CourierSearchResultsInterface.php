<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Retailinsights\CourierAvailability\Api\Data;

interface CourierSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Courier list.
     * @return \Retailinsights\CourierAvailability\Api\Data\CourierInterface[]
     */
    public function getItems();

    /**
     * Set courier_name list.
     * @param \Retailinsights\CourierAvailability\Api\Data\CourierInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

