<?php
namespace Retailinsights\Backorders\Model\ResourceModel\Backorders;

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
		$this->_init('Retailinsights\Backorders\Model\Backorders', 'Retailinsights\Backorders\Model\ResourceModel\Backorders');
	}

}
