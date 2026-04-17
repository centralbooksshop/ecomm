<?php 
namespace Retailinsights\EcomCustom\Model\ResourceModel;

class OptionalBooksReports extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("sales_order","entity_id");
    }
}
 ?>