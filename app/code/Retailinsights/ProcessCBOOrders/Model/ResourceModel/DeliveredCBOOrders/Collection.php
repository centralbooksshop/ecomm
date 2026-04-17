<?php
namespace Retailinsights\ProcessCBOOrders\Model\ResourceModel\DeliveredCBOOrders;

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
		$this->_init('Retailinsights\ProcessCBOOrders\Model\DeliveredCBOOrders', 'Retailinsights\ProcessCBOOrders\Model\ResourceModel\DeliveredCBOOrders');
	}

}
