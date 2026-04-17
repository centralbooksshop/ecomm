<?php


namespace Ecom\Ecomexpress\Model\ResourceModel;

class Pincode extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	public function _construct() {
		$this->_init ( 'ecomexpress_pincode', 'pincode_id' );
	}
}