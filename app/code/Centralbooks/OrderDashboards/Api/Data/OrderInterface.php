<?php
declare(strict_types=1);

namespace Centralbooks\OrderDashboards\Api\Data;

interface OrderInterface
{

    const ENTITYID = 'entity_id';
    const ORDER_ID = 'order_id';

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Centralbooks\OrderDashboards\Order\Api\Data\OrderInterface
     */
    public function setOrderId($orderId);

    /**
     * Get EntityId
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set EntityId
     * @param string $entityId
     * @return \Centralbooks\OrderDashboards\Order\Api\Data\OrderInterface
     */
    public function setEntityId($entityId);
}

