<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection;

class GetCustomerInfo
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param int $orderId
     * @return array
     */
    public function execute(int $orderId): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->resource->getTableName('sales_order')],
                ['customer_id', 'customer_email']
            )
            ->where("main_table.entity_id = " . $orderId);

        return $connection->fetchRow($select) ?: [];
    }

    /**
     * @param int[] $orderIds
     * @return array[]
     */
    public function executeList(array $orderIds): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->resource->getTableName('sales_order')],
                ['entity_id', 'customer_id', 'customer_email']
            )
            ->where("main_table.entity_id IN (" . implode(',', $orderIds) . ")");
        $ordersData = $connection->fetchAll($select);

        return $ordersData ? $this->prepareResult($ordersData) : [];
    }

    /**
     * @param array $orders
     * @return array
     */
    private function prepareResult(array $orders): array
    {
        $results = [];

        foreach ($orders as $order) {
            $orderId = $order['entity_id'];
            unset($order['entity_id']);
            $results[$orderId] = $order;
        }

        return $results;
    }
}
