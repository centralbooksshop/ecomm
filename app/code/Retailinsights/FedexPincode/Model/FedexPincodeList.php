<?php
namespace Retailinsights\FedexPincode\Model;

class FedexPincodeList extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\FedexPincode\Model\ResourceModel\FedexPincodeList');
    }
}
?>