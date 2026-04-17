<?php

namespace SchoolZone\Search\Model;

class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'schools_registered';

	protected $_cacheTag = 'schools_registered';

	protected $_eventPrefix = 'schools_registered';

	protected function _construct()
	{
		$this->_init('SchoolZone\Search\Model\ResourceModel\Post');
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
