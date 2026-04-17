<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class BackgroundOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Background Color')], 
            ['value' => 2, 'label' => __('Background Image')],
            ['value' => 3, 'label' => __('Background Image, Color')],
        ];
    }
}
