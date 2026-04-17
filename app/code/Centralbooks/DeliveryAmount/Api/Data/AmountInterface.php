<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Api\Data;

interface AmountInterface
{

    const ZONES_ID = 'zones_id';

    /**
     * Get zones_id
     * @return string|null
     */
    public function getZonesId();

    /**
     * Set zones_id
     * @param string $zonesId
     * @return \Centralbooks\DeliveryAmount\Amount\Api\Data\AmountInterface
     */
    public function setZonesId($zonesId);
}


