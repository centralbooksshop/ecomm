<?php 

namespace Retailinsights\Backorders\Model;

class Backorders extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Retailinsights\Backorders\Model\ResourceModel\Backorders");
	}
}
 ?>