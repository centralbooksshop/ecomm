<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Model;

use Centralbooks\DeliveryAmount\Api\Data\TransactionsInterface;
use Magento\Framework\Model\AbstractModel;

class Transactions extends AbstractModel implements TransactionsInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\DeliveryAmount\Model\ResourceModel\Transactions::class);
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
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

}



