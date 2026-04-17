<?php
namespace Infomodus\Fedexlabel\Model\Config;

class DimensionsType extends \Infomodus\Fedexlabel\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['value' => 0, 'label' => 'Automatic calculation'],
            ['value' => 1, 'label' => 'Static box'],
        ];

        return $c;
    }
}
