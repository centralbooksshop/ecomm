<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns\Order;

use Plumrocket\RMA\Block\Returns\TemplateTrait;

class Link extends \Magento\Sales\Block\Order\Link
{
    use TemplateTrait;

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->_registry->registry('current_order');
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    protected function _toHtml()
    {
        $order = $this->getOrder();
        if ($order
            && ! $this->returnsHelper->getAllByOrder($order)->getSize()
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
