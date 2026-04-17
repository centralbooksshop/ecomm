<?php
namespace Retailinsights\WalkinCustomers\Model\ResourceModel\WalkinCustomers;

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
		$this->_init('Retailinsights\WalkinCustomers\Model\WalkinCustomers', 'Retailinsights\WalkinCustomers\Model\ResourceModel\WalkinCustomers');
	}

}
