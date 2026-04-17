<?php

namespace Retailinsights\Orderexport\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\Orderexport\Model\Post', 'Retailinsights\Orderexport\Model\ResourceModel\Post');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>