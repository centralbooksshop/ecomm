<?php 

namespace Centralbooks\ClickpostCustom\Model;

class ProcessClickpostOrders extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Centralbooks\ClickpostCustom\Model\ResourceModel\ProcessClickpostOrders");
	}
}