<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Fedexlabel\Block\Adminhtml;

class Address extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'address';
        $this->_headerText = __('Address');
        $this->_addButtonLabel = __('Add New Address');
        parent::_construct();
    }
}
