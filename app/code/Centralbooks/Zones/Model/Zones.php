<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Model;

use Centralbooks\Zones\Api\Data\ZonesInterface;
use Magento\Framework\Model\AbstractModel;

class Zones extends AbstractModel implements ZonesInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\Zones\Model\ResourceModel\Zones::class);
    }

    /**
     * @inheritDoc
     */
    public function getZonesId()
    {
        return $this->getData(self::ZONES_ID);
    }

    /**
     * @inheritDoc
     */
    public function setZonesId($zonesId)
    {
        return $this->setData(self::ZONES_ID, $zonesId);
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @inheritDoc
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }
}

