<?php

namespace Retailinsights\FedexPincode\Model\ResourceModel\FedexPincodeList;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\FedexPincode\Model\FedexPincodeList', 'Retailinsights\FedexPincode\Model\ResourceModel\FedexPincodeList');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>