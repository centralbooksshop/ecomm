<?php
namespace Retailinsights\DtdcCustom\Model;

use Magento\Framework\Model\AbstractModel;

class ProcessDtdcOrders extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders::class);
    }
}
