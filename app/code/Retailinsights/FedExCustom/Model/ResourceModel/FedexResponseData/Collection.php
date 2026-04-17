<?php
namespace Retailinsights\FedExCustom\Model\ResourceModel\FedexResponseData;

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
		$this->_init('Retailinsights\FedExCustom\Model\FedexResponseData', 'Retailinsights\FedExCustom\Model\ResourceModel\FedexResponseData');
	}
}
