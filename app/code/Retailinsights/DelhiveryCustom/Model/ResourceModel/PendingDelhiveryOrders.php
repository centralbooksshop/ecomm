<?php 
namespace Retailinsights\DelhiveryCustom\Model\ResourceModel;

class PendingDelhiveryOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("cbo_assign_shippment","id");
    }
}
 ?>