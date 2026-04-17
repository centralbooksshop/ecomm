<?php
namespace Retailinsights\Adminroles\Model;
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'admin_user';

	protected $_cacheTag = 'admin_user';

	protected $_eventPrefix = 'admin_user';

	protected function _construct()
	{
		$this->_init('Retailinsights\Adminroles\Model\ResourceModel\Post');
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
