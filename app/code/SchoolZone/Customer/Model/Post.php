<?php
namespace SchoolZone\Customer\Model;
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'schools_registered_by_user';

	protected $_cacheTag = 'schools_registered_by_user';

	protected $_eventPrefix = 'schools_registered_by_user';

	protected function _construct()
	{
		$this->_init('SchoolZone\Customer\Model\ResourceModel\Post');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
