<?php
namespace Centralbooks\ClickpostCustom\Block\Adminhtml;

class ProcessClickpostOrder extends \Magento\Framework\View\Element\Template
{
	protected $_postFactory;
	public $_storeManager;
	protected $_customerSession;
	private $collectionFactory;

	public function __construct(
		 \Magento\Customer\Model\Session $session,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Backend\Block\Widget\Context $context
	  )
	{
		$this->_customerSession = $session;
		$this->_storeManager=$storeManager;
		// $this->collectionFactory = $collectionFactory;
		parent::__construct($context);
	}

	
}