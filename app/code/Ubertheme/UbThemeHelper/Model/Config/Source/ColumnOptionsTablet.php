<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class ColumnOptionsTablet implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 2, 'label' => __('2 items')], 
            ['value' => 3, 'label' => __('3 items')],
            ['value' => 4, 'label' => __('4 items')],
            ['value' => 5, 'label' => __('5 items')]
        ];
    }
}
