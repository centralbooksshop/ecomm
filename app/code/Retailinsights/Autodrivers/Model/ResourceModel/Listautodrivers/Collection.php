<?php

namespace Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\Autodrivers\Model\Listautodrivers', 'Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>