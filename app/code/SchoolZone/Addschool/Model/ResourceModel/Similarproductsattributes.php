<?php
namespace SchoolZone\Addschool\Model\ResourceModel;

class Similarproductsattributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('schools_registered', 'id');
    }
}
?>