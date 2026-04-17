<?php
namespace Centralbooks\ClickpostCustom\Model\ResourceModel\ProcessClickpostOrders;

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
      $this->_init('Centralbooks\ClickpostCustom\Model\ProcessClickpostOrders','Centralbooks\ClickpostCustom\Model\ResourceModel\ProcessClickpostOrders');
	}

}
