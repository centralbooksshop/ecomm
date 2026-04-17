<?php
namespace SchoolZone\Registration\Model\ResourceModel;

class Similarproductsattributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('schools_registered_by_user', 'id');
    }
}
?>