<?php
namespace Retailinsights\DelhiveryCustom\Model\ResourceModel\PendingDelhiveryOrders;

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
		$this->_init('Retailinsights\FedExCustom\Model\ProcessFedexOrders', 'Retailinsights\FedExCustom\Model\ResourceModel\PendingFedexOrders');
	}

}
