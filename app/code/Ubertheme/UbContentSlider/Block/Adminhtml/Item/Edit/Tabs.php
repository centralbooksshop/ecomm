<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Block\Adminhtml\Item\Edit;

/**
 * Admin slide item left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('item_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Slide Item Information'));
    }
}
