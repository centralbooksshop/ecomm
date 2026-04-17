<?php
namespace Infomodus\Fedexlabel\Model\Config;

class AlcoholGenOpt implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'CONTENT ON SHIPPING LABEL ONLY', 'value' => 'CONTENT_ON_SHIPPING_LABEL_ONLY'],
            ['label' => 'CONTENT ON SHIPPING LABEL PREFERRED', 'value' => 'CONTENT_ON_SHIPPING_LABEL_PREFERRED'],
            ['label' => 'CONTENT ON SUPPLEMENTAL LABEL ONLY', 'value' => 'CONTENT_ON_SUPPLEMENTAL_LABEL_ONLY'],
        ];
        return $c;
    }
}