<?php
namespace Retailinsights\DelhiveryCustom\Model\ResourceModel\ProcessDelhiveryOrders;

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
      $this->_init('Retailinsights\DelhiveryCustom\Model\ProcessDelhiveryOrders','Retailinsights\DelhiveryCustom\Model\ResourceModel\ProcessDelhiveryOrders');
	}

}
