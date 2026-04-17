<?php
namespace Retailinsights\Cancellayer\Block\Adminhtml\Similarproductsattributes\Edit;

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
        $this->setId('similarproductsattributes_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Cancel Information'));
    }
}