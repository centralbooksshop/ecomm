<?php
namespace Infomodus\Fedexlabel\Model\Config;

class PaperlessType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => 'COMMERCIAL INVOICE', 'value' => 'COMMERCIAL_INVOICE'],
            ['label' => 'PRO FORMA INVOICE', 'value' => 'PRO_FORMA_INVOICE'],
        ];
    }
}