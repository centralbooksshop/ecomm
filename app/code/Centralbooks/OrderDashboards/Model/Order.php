<?php
declare(strict_types=1);

namespace Centralbooks\OrderDashboards\Model;

use Centralbooks\OrderDashboards\Api\Data\OrderInterface;
use Magento\Framework\Model\AbstractModel;

class Order extends AbstractModel implements OrderInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\OrderDashboards\Model\ResourceModel\Order::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITYID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITYID, $entityId);
    }
}

