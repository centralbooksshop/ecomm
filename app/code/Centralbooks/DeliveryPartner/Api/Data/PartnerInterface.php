<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Api\Data;

interface PartnerInterface
{

    const NAME = 'name';
    const PARTNER_ID = 'partner_id';

    /**
     * Get partner_id
     * @return string|null
     */
    public function getPartnerId();

    /**
     * Set partner_id
     * @param string $partnerId
     * @return \Centralbooks\DeliveryPartner\Partner\Api\Data\PartnerInterface
     */
    public function setPartnerId($partnerId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Centralbooks\DeliveryPartner\Partner\Api\Data\PartnerInterface
     */
    public function setName($name);
}

