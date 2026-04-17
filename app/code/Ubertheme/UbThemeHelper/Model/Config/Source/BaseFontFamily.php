<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class BaseFontFamily implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'serif', 'label' => __('serif')], 
            ['value' => 'sans-serif', 'label' => __('sans-serif')],
            ['value' => 'monospace', 'label' => __('monospace')],
            ['value' => 'cursive', 'label' => __('cursive')],
            ['value' => 'fantasy', 'label' => __('fantasy')]
        ];
    }
}
