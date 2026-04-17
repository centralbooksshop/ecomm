<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel\Condition;

use Plumrocket\RMA\Model\Condition;
use Plumrocket\RMA\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 * @method Condition|null getItemById($idValue)
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
        $this->_init(Condition::class, \Plumrocket\RMA\Model\ResourceModel\Condition::class);
    }
}
