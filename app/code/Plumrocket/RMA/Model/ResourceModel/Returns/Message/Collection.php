<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Returns\Message;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

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
            'Plumrocket\RMA\Model\Returns\Message',
            'Plumrocket\RMA\Model\ResourceModel\Returns\Message'
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
}
