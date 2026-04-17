<?php
namespace Infomodus\Fedexlabel\Model\Config;

class Referenceid implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'No', 'value' => ''),
            /*array('label' => 'Shipment ID', 'value' => 'shipment'),*/
            array('label' => 'Order ID', 'value' => 'order'),
        );
        return $c;
    }
}
