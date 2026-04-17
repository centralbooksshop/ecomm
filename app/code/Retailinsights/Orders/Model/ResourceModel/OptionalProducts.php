<?php

namespace Retailinsights\Orders\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OptionalProducts extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('quote_item', 'item_id'); // blog is the database table
    }
}