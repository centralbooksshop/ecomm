<?php 
namespace Retailinsights\FedExCustom\Model\ResourceModel;

class FedexResponseData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        $this->_init("fedex_response","id");
    }
}
 ?>