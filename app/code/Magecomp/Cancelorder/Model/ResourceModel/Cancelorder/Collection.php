<?php
namespace Magecomp\Cancelorder\Model\ResourceModel\Cancelorder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'ordercancel_id';
    public function _construct()
    {
        $this->_init("Magecomp\Cancelorder\Model\Cancelorder", "Magecomp\Cancelorder\Model\ResourceModel\Cancelorder");
    }
}