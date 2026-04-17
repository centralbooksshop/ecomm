<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class LinkDecoration implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'none', 'label' => __('None')],
            ['value' => 'underline', 'label' => __('Underline')],
            ['value' => 'overline', 'label' => __('Overline')],
            ['value' => 'line-through', 'label' => __('Line Through')]
        ];
    }
}
