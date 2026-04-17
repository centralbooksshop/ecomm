<?php 

namespace Retailinsights\FedExCustom\Model;

class ProcessFedexOrders extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Retailinsights\FedExCustom\Model\ResourceModel\ProcessFedexOrders");
	}
}
 ?>