<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Plumrocket\RMA\Controller\AbstractReturns;

class Index extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->_forward('history');
    }

    /**
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        // This page doesn't need return id and allowed only for customers
        return (bool)$this->getCustomer();
    }
}
