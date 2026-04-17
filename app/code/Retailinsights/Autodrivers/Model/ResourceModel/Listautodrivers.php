<?php
namespace Retailinsights\Autodrivers\Model\ResourceModel;

class Listautodrivers extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cboshipping_autodrivers', 'id');
    }
}
?>