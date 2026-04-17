<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class FontWeight implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 100, 'label' => __('Thin 100')], 
            ['value' => 200, 'label' => __('Extra Light 200')],
            ['value' => 300, 'label' => __('Light 300')],
            ['value' => 400, 'label' => __('Regular 400')],
            ['value' => 500, 'label' => __('Medium 500')],
            ['value' => 600, 'label' => __('Semi Bold 600')],
            ['value' => 700, 'label' => __('Bold 700')],
            ['value' => 800, 'label' => __('Black 800')],
            ['value' => 900, 'label' => __('Black 900')],
        ];
    }
}
