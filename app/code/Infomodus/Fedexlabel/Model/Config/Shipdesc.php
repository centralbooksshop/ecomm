<?php
namespace Infomodus\Fedexlabel\Model\Config;

class Shipdesc implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => __('Customer name + Order Id'), 'value' => '1'),
            array('label' => __('Only Customer name'), 'value' => '2'),
            array('label' => __('Only Order Id'), 'value' => '3'),
            array('label' => __('List of Products'), 'value' => '4'),
            array('label' => __('Custom value'), 'value' => '5'),
            array('label' => __('nothing'), 'value' => ''),
        );
        return $c;
    }
}
