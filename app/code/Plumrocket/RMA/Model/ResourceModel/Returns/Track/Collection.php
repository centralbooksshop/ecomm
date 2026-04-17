<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel\Returns\Track;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Plumrocket\RMA\Model\Returns\Track',
            'Plumrocket\RMA\Model\ResourceModel\Returns\Track'
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
        $this->addFieldToFilter('parent_id', $returnsId);
        return $this;
    }
}
