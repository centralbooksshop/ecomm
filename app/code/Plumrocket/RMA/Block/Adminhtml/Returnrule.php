<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml;

class Returnrule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_returnrule';
        $this->_blockGroup = 'Plumrocket_RMA';
        $this->_headerText = __('Return Rule');
        $this->_addButtonLabel = __('Add New Return Rule');
        parent::_construct();
    }
}
