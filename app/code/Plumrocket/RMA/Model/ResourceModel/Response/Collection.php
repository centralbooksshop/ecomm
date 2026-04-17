<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Response;

use Plumrocket\RMA\Api\Data\ResponseTemplateInterface;
use Plumrocket\RMA\Model\ResourceModel\AbstractCollection;
use Plumrocket\RMA\Model\Response;

/**
 * @method ResponseTemplateInterface[]|Response[] getItems()
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
        $this->_init(Response::class, \Plumrocket\RMA\Model\ResourceModel\Response::class);
    }
}
