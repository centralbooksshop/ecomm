<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel\Resolution;

use Plumrocket\RMA\Model\Resolution;
use Plumrocket\RMA\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 * @method Resolution|null getItemById($idValue)
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
        $this->_init(Resolution::class, \Plumrocket\RMA\Model\ResourceModel\Resolution::class);
    }
}
