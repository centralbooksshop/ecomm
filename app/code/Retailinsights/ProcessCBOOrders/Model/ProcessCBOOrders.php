<?php 

namespace Retailinsights\ProcessCBOOrders\Model;

class ProcessCBOOrders extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders");
	}
}
 ?>