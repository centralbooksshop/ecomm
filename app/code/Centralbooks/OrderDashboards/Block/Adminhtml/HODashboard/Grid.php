<?php
namespace Centralbooks\OrderDashboards\Block\Adminhtml\HODashboard;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{

	protected function _construct()
	{
		$this->_controller = 'adminhtml_dashboard';
		$this->_blockGroup = 'Centralbooks\OrderDashboards';
		$this->_headerText = __('Orders');
		parent::_construct();
		$this->buttonList->remove('add');
	}
}