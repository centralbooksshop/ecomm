<?php

namespace Retailinsights\Registers\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Apiprovider implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'smscountry', 'label' => __('SMS Country')],
            ['value' => 'vcon', 'label' => __('VCON')]
        ];
    }
}
