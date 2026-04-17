<?php
namespace Retailinsights\FedexPincode\Model\ResourceModel;

class FedexPincodeList extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fedex_pincode', 'id');
    }
}
?>