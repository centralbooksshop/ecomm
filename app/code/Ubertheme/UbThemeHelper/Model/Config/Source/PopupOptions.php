<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class PopupOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Login Form')],
            ['value' => 1, 'label' => __('Newsletter Subscribe Form')],
            ['value' => 2, 'label' => __('Specify a Static Block')]
        ];
    }
}
