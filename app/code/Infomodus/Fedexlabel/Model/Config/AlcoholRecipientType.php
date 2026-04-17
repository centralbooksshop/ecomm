<?php
namespace Infomodus\Fedexlabel\Model\Config;

class AlcoholRecipientType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => 'CONSUMER', 'value' => 'CONSUMER'],
            ['label' => 'LICENSEE', 'value' => 'LICENSEE'],
        ];
    }
}