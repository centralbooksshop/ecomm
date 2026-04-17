<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Returns\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Plumrocket\RMA\Model\Returns\Item',
            'Plumrocket\RMA\Model\ResourceModel\Returns\Item'
        );
    }

    /**
     * Add filter by returns
     *
     * @param int $returnsId
     * @return $this
     */
    public function addReturnsFilter($returnsId)
    {
        $this->addFieldToFilter('parent_id', (int)$returnsId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        // Prepare numeric values.
        $cols = [
            ItemHelper::QTY_REQUESTED,
            ItemHelper::QTY_AUTHORIZED,
            ItemHelper::QTY_RECEIVED,
            ItemHelper::QTY_APPROVED,
        ];

        foreach ($this->_items as $item) {
            foreach ($cols as $col) {
                if (null !== $item->getData($col)) {
                    $item->setData($col, (int)$item->getData($col));
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Add return data to collection
     *
     * @return $this
     */
    public function addReturnsData()
    {
        $this->join(
            ['r' => $this->getTable('plumrocket_rma_returns')],
            'r.entity_id = main_table.parent_id',
            ['*']
        );
        return $this;
    }

    /**
     * Add filter by order
     *
     * @param int $orderId
     * @return $this
     */
    /*public function addFilterByOrder($orderId)
    {
        $this->join(
            ['i' => 'mage_sales_order_item'],
            'i.item_id = main_table.order_item_id',
            []
        );
        $this->addFieldToFilter('order_id', (int)$orderId);
        return $this;
    }*/
}
