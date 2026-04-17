<?php 
namespace Retailinsights\WalkinCustomers\Model\ResourceModel;

class WalkinCustomers extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("walkin_other_couriers","id");
    }
}
 ?>