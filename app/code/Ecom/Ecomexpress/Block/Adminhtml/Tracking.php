<?php


namespace Ecom\Ecomexpress\Block\Adminhtml;

class Tracking extends \Magento\Backend\Block\Template {

	protected $_coreRegistry = null;
	
	public function __construct(\Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = []) {
		$this->_coreRegistry = $registry;
		parent::__construct ( $context, $data );
		//die('=====');
	}

	/*public function getAwb() {
		return '12345';
	}*/
}
