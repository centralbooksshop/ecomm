<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns\Create;

class Submit extends \Plumrocket\RMA\Block\Returns\Template
{
    /**
     * @return string
     */
    public function getCancelUrl()
    {
        if ($this->isGuestMode()) {
            return $this->getUrl('sales/guest/view', [
                'order_id' => $this->getOrder()->getId()
            ]);
        } else {
            return $this->getUrl('sales/order/view', [
                'order_id' => $this->getOrder()->getId()
            ]);
        }
    }
}
