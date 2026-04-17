<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Fedexlabel\Block\Adminhtml\Items\Edit;

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
        $this->setId('infomodus_fedexlabel_items_edit_tabs');
        $this->setDestElementId('edit_form');
        switch($this->getRequest()->getParam('direction')){
            case 'refund': $label = 'RMA(return) FedEx label';
                break;
            case 'invert': $label = 'Invert FedEx label';
                break;
            default: $label = 'Shipping FedEx label';
        }
        $this->setTitle(__($label));
    }
}
