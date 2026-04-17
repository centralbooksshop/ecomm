<?php
namespace SchoolZone\Review\Controller\Index;

class Display extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$schoolName = $this->getRequest()->getParam('schoolname');
		$this->_objectManager->get(\Magento\Framework\Registry::class)->register('current_school_name', $schoolName);

		return $this->_pageFactory->create();
	}
}
