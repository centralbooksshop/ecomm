<?php
namespace Infomodus\Fedexlabel\Model\Config;
class Unitofmeasurement implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $array = [
            ['label' => 'Inches', 'value' => 'IN'],
            ['label' => 'Centimeters', 'value' => 'CM'],
        ];
        return $array;
    }
    public function getArray()
    {
        $c = [];
        $arr = $this->toOptionArray();
        foreach ($arr as $val) {
            $c[$val['value']] = $val['label'];
        }
        return $c;
    }
}
