<?php
namespace Infomodus\Fedexlabel\Model\Config;
class Weight implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'LBS', 'value' => 'LB'],
            ['label' => 'KGS', 'value' => 'KG'],
        ];
        return $c;
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