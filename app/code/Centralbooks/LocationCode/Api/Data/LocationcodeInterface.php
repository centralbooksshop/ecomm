<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Api\Data;

interface LocationcodeInterface
{

    const NAME = 'name';
    const LOCATIONCODE_ID = 'locationcode_id';

    /**
     * Get locationcode_id
     * @return string|null
     */
    public function getLocationcodeId();

    /**
     * Set locationcode_id
     * @param string $locationcodeId
     * @return \Centralbooks\LocationCode\Locationcode\Api\Data\LocationcodeInterface
     */
    public function setLocationcodeId($locationcodeId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Centralbooks\LocationCode\Locationcode\Api\Data\LocationcodeInterface
     */
    public function setName($name);
}

