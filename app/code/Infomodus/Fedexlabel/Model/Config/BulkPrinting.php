<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
class BulkPrinting implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'All labels', 'value' => 0],
            ['label' => 'Unprinted labels only', 'value' => 1],
        ];
        return $c;
    }
}