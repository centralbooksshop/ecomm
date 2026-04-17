<?php 
namespace Retailinsights\FedExCustom\Model\ResourceModel;

class ProcessFedexOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("cbo_assign_shippment","id");
    }
}
 ?>