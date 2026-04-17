<?php 
namespace Retailinsights\Backorders\Model\ResourceModel;

class Backorders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("backorder_items","id");
    }
}
 ?>