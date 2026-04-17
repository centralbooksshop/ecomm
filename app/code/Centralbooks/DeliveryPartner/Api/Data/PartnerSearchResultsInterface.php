<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Api\Data;

interface PartnerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Partner list.
     * @return \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

