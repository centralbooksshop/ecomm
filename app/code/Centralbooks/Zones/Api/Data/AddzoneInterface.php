<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Api\Data;

interface AddzoneInterface
{

    const CONTENT = 'content';
    const ZONES_ID = 'zones_id';

    /**
     * Get zones_id
     * @return string|null
     */
    public function getZonesId();

    /**
     * Set zones_id
     * @param string $zonesId
     * @return \Centralbooks\Zones\Zones\Api\Data\AddzoneInterface
     */
    public function setZonesId($zonesId);

    /**
     * Get content
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     * @param string $content
     * @return \Centralbooks\Zones\Zones\Api\Data\AddzoneInterface
     */
    public function setContent($content);
}

