<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class GalleryNavStyle implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'thumbs', 'label' => __('Thumbnails')], 
            ['value' => 'dots', 'label' => __('Dots')],
            ['value' => 'false', 'label' => __('None')]
        ];
    }
}