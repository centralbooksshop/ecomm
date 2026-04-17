<?php
namespace Retailinsights\Orders\Model\ResourceModel\Order\Item;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Grid extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order_item', 'item_id'); // table, primary key
    }
}
