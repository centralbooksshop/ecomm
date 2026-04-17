<?php
namespace Retailinsights\Autodrivers\Block\Adminhtml\Listautodrivers\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('listautodrivers_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Auto Driver Information'));
    }
}