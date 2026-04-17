<?php 
namespace Retailinsights\EcomCustom\Model\ResourceModel;

class PendingEcomOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("cbo_assign_shippment","id");
    }
}
 ?>