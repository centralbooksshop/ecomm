<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Model;

use Centralbooks\Zones\Api\Data\AddzoneInterface;
use Magento\Framework\Model\AbstractModel;

class Addzone extends AbstractModel implements AddzoneInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\Zones\Model\ResourceModel\Addzone::class);
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

