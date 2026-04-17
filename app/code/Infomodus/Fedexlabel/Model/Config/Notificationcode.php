<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Notificationcode implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'ON DELIVERY', 'value' => 'ON_DELIVERY'],
            ['label' => 'ON EXCEPTION', 'value' => 'ON_EXCEPTION'],
            ['label' => 'ON SHIPMENT', 'value' => 'ON_SHIPMENT'],
            ['label' => 'ON TENDER', 'value' => 'ON_TENDER'],
            ['label' => 'ON ESTIMATED DELIVERY', 'value' => 'ON_ESTIMATED_DELIVERY'],
        ];
        return $c;
    }
}