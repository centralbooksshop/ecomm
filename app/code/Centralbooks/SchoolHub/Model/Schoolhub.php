<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Model;

use Centralbooks\SchoolHub\Api\Data\SchoolhubInterface;
use Magento\Framework\Model\AbstractModel;

class Schoolhub extends AbstractModel implements SchoolhubInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\SchoolHub\Model\ResourceModel\Schoolhub::class);
    }

    /**
     * @inheritDoc
     */
    public function getSchoolhubId()
    {
        return $this->getData(self::SCHOOLHUB_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSchoolhubId($schoolhubId)
    {
        return $this->setData(self::SCHOOLHUB_ID, $schoolhubId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
}

