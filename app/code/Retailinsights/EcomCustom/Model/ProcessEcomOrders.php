<?php 

namespace Retailinsights\EcomCustom\Model;

class ProcessEcomOrders extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Retailinsights\EcomCustom\Model\ResourceModel\ProcessEcomOrders");
	}
}
 ?>