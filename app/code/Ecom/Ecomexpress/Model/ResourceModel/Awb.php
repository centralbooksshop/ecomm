<?php


namespace Ecom\Ecomexpress\Model\ResourceModel;

class Awb extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	public function _construct() {
		$this->_init ( 'ecomexpress_awb', 'awb_id' );
	}
}