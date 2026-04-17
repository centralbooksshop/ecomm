<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class TinType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('Not use'), 'value' => ''],
            ['label' => __('BUSINESS_NATIONAL'), 'value' => 'BUSINESS_NATIONAL'],
            ['label' => __('BUSINESS_STATE'), 'value' => 'BUSINESS_STATE'],
            ['label' => __('BUSINESS_UNION'), 'value' => 'BUSINESS_UNION'],
            ['label' => __('PERSONAL_NATIONAL'), 'value' => 'PERSONAL_NATIONAL'],
            ['label' => __('PERSONAL_STATE'), 'value' => 'PERSONAL_STATE'],
        ];
        return $c;
    }
}