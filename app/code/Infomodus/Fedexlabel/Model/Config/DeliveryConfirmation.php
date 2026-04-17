<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class DeliveryConfirmation implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('ADULT'), 'value' => 'ADULT'],
            ['label' => __('DIRECT'), 'value' => 'DIRECT'],
            ['label' => __('INDIRECT'), 'value' => 'INDIRECT'],
            ['label' => __('NO_SIGNATURE_REQUIRED'), 'value' => 'NO_SIGNATURE_REQUIRED'],
            ['label' => __('SERVICE_DEFAULT'), 'value' => 'SERVICE_DEFAULT'],
        ];
        return $c;
    }
}