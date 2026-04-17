<?php
namespace SchoolZone\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        return [
            'New' => __('New'),
            'In Progress' => __('In Progress'),
            'Hold' => __('Hold'),
            'Closed' => __('Closed'),
        ];
    }
}