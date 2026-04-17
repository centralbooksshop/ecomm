<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Grid;

class Massaction extends \Magento\Backend\Block\Widget\Grid\Massaction
{
    /**
     * @return string
     */
    public function getJavaScript()
    {
        return ' requirejs(["prgrid"]); ' . parent::getJavaScript();
    }
}
