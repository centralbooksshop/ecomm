<?php

namespace SchoolZone\Search\ViewModel;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Session\SessionManagerInterface;

class BlockHelper implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

		protected $_httpContext;
		protected $_sessionManager;

		public function __construct(Context $httpContext, SessionManagerInterface $session)
		{
			$this->_httpContext = $httpContext;
			$this->_sessionManager = $session;
		}

		public function getSessionData()
		{
			return $this->_sessionManager->getData();
		}
        
		public function getCoreSessionData()
		{
			return $_SESSION;
		}
}



