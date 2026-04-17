<?php
namespace Retailinsights\CourierAvailability\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AvailabilityOptions implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')]
        ];
    }
    
    /**
     * Get options in key-value format for grid
     *
     * @return array
     */
    public function toArray()
    {
        return [
            1 => __('Yes'),
            0 => __('No')
        ];
    }
}