<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns\PrintPage;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Items extends \Plumrocket\RMA\Block\Returns\Items
{
    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];
        foreach (parent::getItems() as $item) {
            $status = $this->itemHelper->getStatus($item);
            if (in_array($status, array_keys($this->status->getFinalStatuses()))
                || $status === ReturnsStatus::STATUS_NEW
            ) {
                continue;
            }

            if ($this->itemHelper->isVirtual($item->getOrderItem())) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}
