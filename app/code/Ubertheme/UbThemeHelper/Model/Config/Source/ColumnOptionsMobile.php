<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class ColumnOptionsMobile implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('1 item')],
            ['value' => 2, 'label' => __('2 items')],
            ['value' => 3, 'label' => __('3 items')]
        ];
    }
}
