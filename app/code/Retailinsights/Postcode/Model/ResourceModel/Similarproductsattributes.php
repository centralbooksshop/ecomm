<?php
namespace Retailinsights\Postcode\Model\ResourceModel;

class Similarproductsattributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('managepostcode', 'id');
    }
}
?>