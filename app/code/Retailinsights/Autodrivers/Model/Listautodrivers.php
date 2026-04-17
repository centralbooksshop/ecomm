<?php
namespace Retailinsights\Autodrivers\Model;

class Listautodrivers extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers');
    }
}
?>