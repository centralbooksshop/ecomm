<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml;

class Import extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     * @return void
     */
    
    protected function _construct()
    {
        $this->_controller ='adminhtml_import';
        $this->_blockGroup = 'Cynoinfotech_StorePickup';
        $this->_headerText =__('Import');
        $this->_addButtonLabel = __('Create New Import');
        parent::_construct();
    }
}
