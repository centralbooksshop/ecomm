<?php
namespace Retailinsights\Registers\Block\Index;
class Header extends \Magento\Framework\View\Element\Html\Links
{
	protected $_postFactory;
	public $_storeManager;
	protected $_customerSession;

	public function __construct(
		 \Magento\Customer\Model\Session $session,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\View\Element\Template\Context $context
		
	)
	{
		$this->_customerSession = $session;
		$this->_storeManager=$storeManager;
		parent::__construct($context);
	}

	public function getCustUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
	}
	public function getLoggedIn(){
		if ($this->_customerSession->isLoggedIn()) {
		    return "yes";
		} else {
			return "no";
		}
	}

	
}