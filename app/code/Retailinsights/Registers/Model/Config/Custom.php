<?php
namespace Retailinsights\Registers\Model\Config;
 
class Custom implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('India')]
            ];
    }
}