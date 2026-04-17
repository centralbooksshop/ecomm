<?php

namespace Retailinsights\Cancellayer\Model\ResourceModel\Similarproductsattributes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\Cancellayer\Model\Similarproductsattributes', 'Retailinsights\Cancellayer\Model\ResourceModel\Similarproductsattributes');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>