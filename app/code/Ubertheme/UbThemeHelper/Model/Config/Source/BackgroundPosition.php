<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class BackgroundPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "left top", 'label' => __('Left Top')],
            ['value' => "left bottom", 'label' => __('Left Bottom')],
            ['value' => "right top", 'label' => __('Right Top')],
            ['value' => "right center", 'label' => __('Right Center')],
            ['value' => "right bottom", 'label' => __('Right Bottom')],
            ['value' => "center top", 'label' => __('Center Top')],
            ['value' => "center center", 'label' => __('Center Center')],
            ['value' => "center bottom", 'label' => __('Center Bottom')],
            ['value' => "other", 'label' => __('Other')],
        ];
    }
}
