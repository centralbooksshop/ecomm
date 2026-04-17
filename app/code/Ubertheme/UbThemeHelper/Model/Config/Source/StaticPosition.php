<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class StaticPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Side Bar')],
            ['value' => 2, 'label' => __('Before Product List')], 
            ['value' => 3, 'label' => __('After Product List')],
            ['value' => 4, 'label' => __('Top All Page')],
            ['value' => 5, 'label' => __('Hide All')],
        ];
    }
}
