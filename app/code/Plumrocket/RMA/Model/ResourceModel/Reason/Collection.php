<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel\Reason;

use Plumrocket\RMA\Model\Reason;
use Plumrocket\RMA\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 * @method Reason|null getItemById($idValue)
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Reason::class, \Plumrocket\RMA\Model\ResourceModel\Reason::class);
    }

    /**
     * Add filter by owner payer
     *
     * @return $this
     */
    public function addPayerOwnerFilter()
    {
        $this->addFieldToFilter('payer', Reason::PAYER_OWNER);
        return $this;
    }
}
