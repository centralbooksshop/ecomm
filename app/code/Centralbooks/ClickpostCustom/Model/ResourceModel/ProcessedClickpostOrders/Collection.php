<?php
namespace Centralbooks\ClickpostCustom\Model\ResourceModel\ProcessedClickpostOrders;

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
      $this->_init('Centralbooks\ClickpostCustom\Model\ProcessedClickpostOrders','Centralbooks\ClickpostCustom\Model\ResourceModel\ProcessedClickpostOrders');
	}

}
