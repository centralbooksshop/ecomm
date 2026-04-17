<?php
namespace Retailinsights\ProcessCBOOrders\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Reason extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Retailinsights\ProcessCBOOrders\Model\ResourceModel\Reason");
	}
}
