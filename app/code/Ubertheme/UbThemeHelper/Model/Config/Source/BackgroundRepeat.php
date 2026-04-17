<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class BackgroundRepeat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'repeat', 'label' => __('repeat')], 
            ['value' => 'repeat-x', 'label' => __('repeat-x')],
            ['value' => 'repeat-y', 'label' => __('repeat-y')],
            ['value' => 'no-repeat', 'label' => __('no-repeat')],
            ['value' => 'initial', 'label' => __('initial')],
        ];
    }
}
