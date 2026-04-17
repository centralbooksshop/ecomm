<?php
/**
 * Copyright © 2019 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbThemeHelper\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class InfiniteOptions implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'click', 'label' => __('Load More Button')],
            ['value' => 'scroll', 'label' => __('Infinite Scrolling')],
            ['value' => 'scroll_limit', 'label' => __('Infinite Scrolling Page Limit')],
        ];
    }
}
