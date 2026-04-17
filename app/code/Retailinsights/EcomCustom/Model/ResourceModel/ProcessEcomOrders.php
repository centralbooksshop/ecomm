<?php 
namespace Retailinsights\EcomCustom\Model\ResourceModel;

class ProcessEcomOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("cbo_assign_shippment","id");
    }
}
 ?>