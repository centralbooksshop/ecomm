<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class FontStyle implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "normal", 'label' => __('Normal')],
            ['value' => "italic", 'label' => __('Italic')],
            ['value' => "inherit", 'label' => __('Inherit')],
        ];
    }
}
