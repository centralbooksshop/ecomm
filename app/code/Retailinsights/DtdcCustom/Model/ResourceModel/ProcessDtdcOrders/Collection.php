<?php
namespace Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            \Retailinsights\DtdcCustom\Model\ProcessDtdcOrders::class,
            \Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders::class
        );
    }
}
