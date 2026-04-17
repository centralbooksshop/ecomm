<?php

namespace SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SchoolZone\Addschool\Model\Similarproductsattributes', 'SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>