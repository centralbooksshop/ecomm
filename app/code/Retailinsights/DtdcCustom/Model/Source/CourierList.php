<?php
namespace Retailinsights\DtdcCustom\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CourierList implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'DTDC',       'label' => 'DTDC'],
            ['value' => 'Delhivery',  'label' => 'Delhivery'],
            ['value' => 'Elasticrun', 'label' => 'Elasticrun'],
        ];
    }
}
