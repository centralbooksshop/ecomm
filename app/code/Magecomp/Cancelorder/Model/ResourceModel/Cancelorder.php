<?php
namespace Magecomp\Cancelorder\Model\ResourceModel;

class Cancelorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magecomp_ordercancel','ordercancel_id');
    }
}