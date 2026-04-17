<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class BackgroundSize implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "auto", 'label' => __('Auto')],
            ['value' => "cover", 'label' => __('Cover')],
            ['value' => "contain", 'label' => __('Contain')],
            ['value' => "other", 'label' => __('Other')],
        ];
    }
}
