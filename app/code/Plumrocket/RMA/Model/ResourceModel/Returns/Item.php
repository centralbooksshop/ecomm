<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel\Returns;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;

class Item extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumrocket_rma_returns_item', 'entity_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /**@var $object \Plumrocket\RMA\Model\Returns\Item */
        if (! $object->getParentId() && $object->getReturns()) {
            $object->setParentId($object->getReturns()->getId());
        }

        if (! $object->getOrderItemId() && $object->getOrderItem()) {
            $object->setOrderItemId($object->getOrderItem()->getId());
        }

        // Prepare numeric values.
        $cols = [
            ItemHelper::QTY_REQUESTED,
            ItemHelper::QTY_AUTHORIZED,
            ItemHelper::QTY_RECEIVED,
            ItemHelper::QTY_APPROVED,
        ];

        foreach ($cols as $col) {
            if ('' === $object->getData($col)) {
                $object->unsetData($col);
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        // Prepare numeric values.
        $cols = [
            ItemHelper::QTY_REQUESTED,
            ItemHelper::QTY_AUTHORIZED,
            ItemHelper::QTY_RECEIVED,
            ItemHelper::QTY_APPROVED,
        ];

        foreach ($cols as $col) {
            if (null !== $object->getData($col)) {
                $object->setData($col, (int)$object->getData($col));
            }
        }

        return parent::_afterLoad($object);
    }
}
