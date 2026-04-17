<?php
declare(strict_types=1);

namespace Centralbooks\OrderDashboards\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('sales_order_grid', 'entity_id');
    }
}

