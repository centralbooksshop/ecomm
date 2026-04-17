<?php
namespace SchoolZone\Customer\Model\ResourceModel\Postadd;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'schools_registered_by_user_post_collection';
	protected $_eventObject = 'post_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('SchoolZone\Customer\Model\Postadd', 'SchoolZone\Customer\Model\ResourceModel\Postadd');
	}

}
