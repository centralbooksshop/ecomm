<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class GalleryEffect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'slide', 'label' => __('Slide')], 
            ['value' => 'crossfade', 'label' => __('Crossfade')],
            ['value' => 'dissolve', 'label' => __('Dissolve')]
        ];
    }
}
