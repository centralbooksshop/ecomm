<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class BtnCartAndPrice implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Show Price and Btn Cart')],
            ['value' => 2, 'label' => __('Show Price and Hide Btn Cart')], 
            ['value' => 3, 'label' => __('Hide Price and Hide Btn Cart')],
        ];
    }
}
