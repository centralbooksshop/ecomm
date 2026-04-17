<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Fedexpackagecode implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'FEDEX_ENVELOPE', 'label' => __('FedEx Envelope')],
            ['value' => 'FEDEX_PAK', 'label' => __('FedEx Pak')],
            ['value' => 'FEDEX_BOX', 'label' => __('FedEx Box')],
            ['value' => 'FEDEX_TUBE', 'label' => __('FedEx Tube')],
            ['value' => 'FEDEX_10KG_BOX', 'label' => __('FedEx 10kg Box')],
            ['value' => 'FEDEX_25KG_BOX', 'label' => __('FedEx 25kg Box')],
            ['value' => 'FEDEX_SMALL_BOX', 'label' => __('FedEx Small Box')],
            ['value' => 'FEDEX_MEDIUM_BOX', 'label' => __('FedEx Medium Box')],
            ['value' => 'FEDEX_LARGE_BOX', 'label' => __('FedEx Large Box')],
            ['value' => 'FEDEX_EXTRA_LARGE_BOX', 'label' => __('FedEx Extra Large Box')],
            ['value' => 'YOUR_PACKAGING', 'label' => __('Your Packaging')],
        ];
    }

    public function getPackagingtypecode()
    {
        $c = [];
        $arr = $this->toOptionArray();
        foreach ($arr as $val) {
            $c[$val['value']] = $val['label'];
        }
        return $c;
    }
}