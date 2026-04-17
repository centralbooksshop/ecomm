<?php


namespace Ecom\Ecomexpress\Model;

class Awb extends \Magento\Framework\Model\AbstractModel {
	public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = []) {
		parent::__construct ( $context, $registry, $resource, $resourceCollection, $data );
	}
	public function _construct() {
		$this->_init ( 'Ecom\Ecomexpress\Model\ResourceModel\Awb' );
	}
	public function loadByAwb($awb)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$readConnection = $resource->getConnection();
		$query = "SELECT * FROM " . $resource->getTableName('ecomexpress_awb')." WHERE awb = $awb";
		$data = $readConnection->fetchOne($query);
		return $data;
	}
}