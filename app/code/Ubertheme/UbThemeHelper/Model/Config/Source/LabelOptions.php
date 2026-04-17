<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class LabelOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Use text label')], 
            ['value' => 2, 'label' => __('Use image label')],
            ['value' => 3, 'label' => __('Hide label')],
        ];
    }
}
