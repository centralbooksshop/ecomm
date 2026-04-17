<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Fedexlabel\Block\Adminhtml;

class Conformity extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'conformity';
        $this->_headerText = __('Compliance');
        $this->_addButtonLabel = __('Add New Compliance');
        parent::_construct();
    }
}
