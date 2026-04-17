<?php
declare(strict_types=1);

namespace Centralbooks\SchoolCode\Model;

use Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface;
use Magento\Framework\Model\AbstractModel;

class Schoolcode extends AbstractModel implements SchoolcodeInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\SchoolCode\Model\ResourceModel\Schoolcode::class);
    }

    /**
     * @inheritDoc
     */
    public function getSchoolcodeId()
    {
        return $this->getData(self::SCHOOLCODE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSchoolcodeId($schoolcodeId)
    {
        return $this->setData(self::SCHOOLCODE_ID, $schoolcodeId);
    }

    /**
     * @inheritDoc
     */
    public function getSchoolName()
    {
        return $this->getData(self::SCHOOL_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setSchoolName($schoolName)
    {
        return $this->setData(self::SCHOOL_NAME, $schoolName);
    }
}

