<?php

namespace Ecom\Ecomexpress\Model\ResourceModel\Pincode;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	public function _construct() {
		$this->_init ( 'Ecom\Ecomexpress\Model\Pincode', 'Ecom\Ecomexpress\Model\ResourceModel\Pincode' );
	}
}