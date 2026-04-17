<?php
namespace Retailinsights\Orders\Model;
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'invoice_download_count';

	protected $_cacheTag = 'invoice_download_count';

	protected $_eventPrefix = 'invoice_download_count';

	protected function _construct()
	{
		$this->_init('Retailinsights\Orders\Model\ResourceModel\Post');
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
