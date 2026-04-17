<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class BorderStyle implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'solid', 'label' => __('Solid')],
            ['value' => 'dotted', 'label' => __('Dotted')],
            ['value' => 'dashed', 'label' => __('Dashed')],
            ['value' => 'double', 'label' => __('Double')],
            ['value' => 'groove', 'label' => __('Groove')],
            ['value' => 'ridge', 'label' => __('Ridge')],
            ['value' => 'inset', 'label' => __('Inset')],
            ['value' => 'outset', 'label' => __('Outset')]
        ];
    }
}
