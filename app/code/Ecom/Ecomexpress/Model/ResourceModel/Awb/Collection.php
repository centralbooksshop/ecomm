<?php


namespace Ecom\Ecomexpress\Model\ResourceModel\Awb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	public function _construct() {
		$this->_init ( 'Ecom\Ecomexpress\Model\Awb', 'Ecom\Ecomexpress\Model\ResourceModel\Awb' );
	}
}