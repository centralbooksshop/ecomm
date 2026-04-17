<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Model;

use Centralbooks\DeliveryAmount\Api\Data\AmountInterface;
use Magento\Framework\Model\AbstractModel;

class Amount extends AbstractModel implements AmountInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\DeliveryAmount\Model\ResourceModel\Amount::class);
    }
	
    /**
     * Load object data
     *
     * @param int $id
     * @param null|string $field
     * @return self
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteDeliveryboy();
        }
        return parent::load($id, $field);
    }

    /**
     * Return No route deliveryboy.
     *
     * @return self
     */
    public function noRouteDeliveryboy()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
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
}


