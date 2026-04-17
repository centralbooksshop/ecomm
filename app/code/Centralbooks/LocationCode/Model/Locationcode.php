<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Model;

use Centralbooks\LocationCode\Api\Data\LocationcodeInterface;
use Magento\Framework\Model\AbstractModel;

class Locationcode extends AbstractModel implements LocationcodeInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\LocationCode\Model\ResourceModel\Locationcode::class);
    }

    /**
     * @inheritDoc
     */
    public function getLocationcodeId()
    {
        return $this->getData(self::LOCATIONCODE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLocationcodeId($locationcodeId)
    {
        return $this->setData(self::LOCATIONCODE_ID, $locationcodeId);
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

