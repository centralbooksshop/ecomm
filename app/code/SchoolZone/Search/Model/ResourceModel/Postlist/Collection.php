<?php

namespace SchoolZone\Search\Model\ResourceModel\Postlist;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'schools_registered_collection';
	protected $_eventObject = 'post_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('SchoolZone\Search\Model\Postlist', 'SchoolZone\Search\Model\ResourceModel\Postlist');
	}
}
