<?php
namespace SchoolZone\Customer\Model\ResourceModel;


class Postadd extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('schools_registered', 'id');
	}
	
}
