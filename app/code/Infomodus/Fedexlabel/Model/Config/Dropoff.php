<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Dropoff implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('Regular Pickup'), 'value' => 'REGULAR_PICKUP'],
            ['label' => __('Request Courier'), 'value' => 'REQUEST_COURIER'],
            ['label' => __('Drop Box'), 'value' => 'DROP_BOX'],
            ['label' => __('Business Service Center'), 'value' => 'BUSINESS_SERVICE_CENTER'],
            ['label' => __('Station'), 'value' => 'STATION'],
        ];
        return $c;
    }
}