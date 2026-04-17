<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml;

class StorePickupOrder extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_storepickuporder';
        $this->_blockGroup = 'Cynoinfotech_StorePickup';
        $this->_headerText = __('StorePickup Order');
        $this->_addButtonLabel = __('Create New store order');
        parent::_construct();
    }
}
