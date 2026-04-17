<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
class PurposeOfShipment implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => __('GIFT'), 'value' => 'GIFT'),
            array('label' => __('NOT SOLD'), 'value' => 'NOT_SOLD'),
            array('label' => __('PERSONAL EFFECTS'), 'value' => 'PERSONAL_EFFECTS'),
            array('label' => __('REPAIR AND RETURN'), 'value' => 'REPAIR_AND_RETURN'),
            array('label' => __('SAMPLE'), 'value' => 'SAMPLE'),
            array('label' => __('SOLD'), 'value' => 'SOLD'),
        );
        return $c;
    }
}