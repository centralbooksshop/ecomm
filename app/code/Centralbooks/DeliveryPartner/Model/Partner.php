<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Model;

use Centralbooks\DeliveryPartner\Api\Data\PartnerInterface;
use Magento\Framework\Model\AbstractModel;

class Partner extends AbstractModel implements PartnerInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\DeliveryPartner\Model\ResourceModel\Partner::class);
    }

    /**
     * @inheritDoc
     */
    public function getPartnerId()
    {
        return $this->getData(self::PARTNER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPartnerId($partnerId)
    {
        return $this->setData(self::PARTNER_ID, $partnerId);
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

