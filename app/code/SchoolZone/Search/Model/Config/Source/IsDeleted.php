<?php
namespace SchoolZone\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class IsDeleted implements ArrayInterface
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
            'true' => __('True'),
            'false' => __('False'),
        ];
    }
}