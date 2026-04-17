<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml;

class StorePickup extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_storepickup';
        $this->_blockGroup = 'Cynoinfotech_StorePickup';
        $this->_headerText = __('StorePickup');
        $this->_addButtonLabel = __('Create New store');
        parent::_construct();
    }
}
