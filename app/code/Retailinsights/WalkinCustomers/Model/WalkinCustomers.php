<?php 

namespace Retailinsights\WalkinCustomers\Model;

class WalkinCustomers extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Retailinsights\WalkinCustomers\Model\ResourceModel\WalkinCustomers");
	}
}
 ?>