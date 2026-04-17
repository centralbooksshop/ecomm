<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Fedexlabel\Block\Adminhtml\Address\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('infomodus_fedexlabel_address_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Address'));
    }
}
