<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Api\Data;

interface SchoolhubInterface
{

    const NAME = 'name';
    const SCHOOLHUB_ID = 'schoolhub_id';

    /**
     * Get schoolhub_id
     * @return string|null
     */
    public function getSchoolhubId();

    /**
     * Set schoolhub_id
     * @param string $schoolhubId
     * @return \Centralbooks\SchoolHub\Schoolhub\Api\Data\SchoolhubInterface
     */
    public function setSchoolhubId($schoolhubId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Centralbooks\SchoolHub\Schoolhub\Api\Data\SchoolhubInterface
     */
    public function setName($name);
}

