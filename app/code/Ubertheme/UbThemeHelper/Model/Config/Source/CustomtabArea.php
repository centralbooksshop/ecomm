<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class CustomtabArea implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('All Product IDs')],
            ['value' => 3, 'label' => __('Product ID(s)')],
            ['value' => 2, 'label' => __('Category')], 
            ['value' => 4, 'label' => __('Hide Tab')],
        ];
    }
    public function toArray()
    {
        return [1 => __('All Product IDs'), 2 => __('Category'), 3 => __('Product ID(s)'), 4 => __('Hide Tab')];
    }
}
