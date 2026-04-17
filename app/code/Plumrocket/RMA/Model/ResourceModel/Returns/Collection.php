<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Returns;

use Plumrocket\RMA\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    use CollectionTrait;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\Returns', 'Plumrocket\RMA\Model\ResourceModel\Returns');
    }

    /**
     * Add filter for not archive returns
     *
     * @return $this
     */
    public function addNotArchiveFilter()
    {
        $this->addFieldToFilter('main_table.is_closed', false);
        return $this;
    }

    /**
     * Add filter for archive returns
     *
     * @return $this
     */
    public function addArchiveFilter()
    {
        $this->addFieldToFilter('main_table.is_closed', true);
        return $this;
    }
}
