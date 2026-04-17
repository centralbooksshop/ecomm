<?php 
namespace Retailinsights\FedExCustom\Model\ResourceModel;

class PendingFedexOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("cbo_assign_shippment","id");
    }
}
 ?>