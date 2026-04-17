<?php
declare(strict_types=1);

namespace Centralbooks\SchoolCode\Api\Data;

interface SchoolcodeInterface
{

    const SCHOOL_NAME = 'school_name';
    const SCHOOLCODE_ID = 'schoolcode_id';

    /**
     * Get schoolcode_id
     * @return string|null
     */
    public function getSchoolcodeId();

    /**
     * Set schoolcode_id
     * @param string $schoolcodeId
     * @return \Centralbooks\SchoolCode\Schoolcode\Api\Data\SchoolcodeInterface
     */
    public function setSchoolcodeId($schoolcodeId);

    /**
     * Get school_name
     * @return string|null
     */
    public function getSchoolName();

    /**
     * Set school_name
     * @param string $schoolName
     * @return \Centralbooks\SchoolCode\Schoolcode\Api\Data\SchoolcodeInterface
     */
    public function setSchoolName($schoolName);
}

