<?php
namespace SchoolZone\Customer\Block;
class Login extends \Magento\Framework\View\Element\Template
{
	protected $_orderCollectionFactory;

	public function __construct(
	\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory 
	)
	{
		$this->_orderCollectionFactory = $orderCollectionFactory;
		parent::__construct($context);
	}

	public function sayHello()
	{
		return __('Hello World');
	}
	 
}
?>
