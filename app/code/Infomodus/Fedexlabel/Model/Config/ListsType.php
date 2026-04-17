<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
namespace Infomodus\Fedexlabel\Model\Config;

class ListsType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'shipment', 'label' => __('Shipment')],
            ['value' => 'refund', 'label' => __('Refund')],
            ['value' => 'invert', 'label' => __('Invert shipment')],
        ];
    }

    public function getTypes()
    {
        return [
            'shipment' => __('Shipment'),
            'refund' => __('Refund'),
            'invert' => __('Invert shipment'),
        ];
    }
}
