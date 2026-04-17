<?php
namespace Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders;

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
		$this->_init('Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrders', 'Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders');
	}

}
