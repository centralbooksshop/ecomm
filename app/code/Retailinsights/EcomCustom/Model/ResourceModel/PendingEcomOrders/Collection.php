<?php
namespace Retailinsights\EcomCustom\Model\ResourceModel\PendingEcomOrders;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */

	protected $_idFieldName = 'id';
	protected function _construct()
	{
		$this->_init('Retailinsights\EcomCustom\Model\ProcessEcomOrders', 'Retailinsights\EcomCustom\Model\ResourceModel\PendingEcomOrders');
	}

}
