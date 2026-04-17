<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel;

/**
 * Interface for all entities, such as: reason, condition, rools etc
 */
interface EntityResourceInterface
{
    /**
     * Retrieve entity type id
     * @return int
     */
    public function getEntityTypeId();
}
