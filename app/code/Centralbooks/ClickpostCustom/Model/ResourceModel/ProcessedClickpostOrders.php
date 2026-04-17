<?php 
namespace Centralbooks\ClickpostCustom\Model\ResourceModel;

class ProcessedClickpostOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("cbo_assign_shippment","id");
    }
}