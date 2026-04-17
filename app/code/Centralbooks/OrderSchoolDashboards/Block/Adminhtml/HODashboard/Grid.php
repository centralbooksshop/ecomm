<?php
namespace Centralbooks\OrderSchoolDashboards\Block\Adminhtml\HODashboard;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{

	protected function _construct()
	{
		$this->_controller = 'adminhtml_dashboard';
		$this->_blockGroup = 'Centralbooks\OrderSchoolDashboards';
		$this->_headerText = __('School Orders');
		parent::_construct();
		$this->buttonList->remove('add');
	}
}