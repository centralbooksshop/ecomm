<?php 
namespace Retailinsights\ProcessCBOOrders\Model\ResourceModel;

class DeliveredCBOOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("cbo_assign_shippment","id");
    }
}
 ?>