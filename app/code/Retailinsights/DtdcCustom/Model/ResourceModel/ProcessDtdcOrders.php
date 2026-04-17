<?php
namespace Retailinsights\DtdcCustom\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProcessDtdcOrders extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('cbo_assign_shippment', 'id'); // table name, primary key
    }
}
