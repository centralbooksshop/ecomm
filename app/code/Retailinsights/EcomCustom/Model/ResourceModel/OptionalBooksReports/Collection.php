<?php
namespace Retailinsights\EcomCustom\Model\ResourceModel\OptionalBooksReports;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */

	protected $_idFieldName = 'entity_id';
	protected function _construct()
	{
		$this->_init('Retailinsights\EcomCustom\Model\OptionalBooksReports', 'Retailinsights\EcomCustom\Model\ResourceModel\OptionalBooksReports');
	}

}
