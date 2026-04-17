<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model\ResourceModel\Returns;

use Magento\Framework\App\ResourceConnection;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

/**
 * Search cancelled returns ids for order item
 *
 * @since 2.2.3
 */
class GetCanceledByOrderItem
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /***
     * @param int $orderItemId
     * @return int[]
     */
    public function execute(int $orderItemId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection
            ->select()
            ->from(
                ['return' => $this->resourceConnection->getTableName('plumrocket_rma_returns')],
                ['entity_id']
            )
            ->joinLeft(
                [
                    'return_item' => $this->resourceConnection->getTableName('plumrocket_rma_returns_item'),
                ],
                'return.entity_id = return_item.parent_id'
            )
            ->where('return.status = ?', ReturnsStatus::STATUS_CANCELLED)
            ->where('return_item.order_item_id = ?', $orderItemId);

        return array_map(
            static function ($returnId) {
                return (int) $returnId;
            },
            $connection->fetchCol($select)
        );
    }
}
