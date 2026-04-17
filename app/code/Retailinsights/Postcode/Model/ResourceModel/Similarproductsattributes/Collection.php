<?php

namespace Retailinsights\Postcode\Model\ResourceModel\Similarproductsattributes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\Postcode\Model\Similarproductsattributes', 'Retailinsights\Postcode\Model\ResourceModel\Similarproductsattributes');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>