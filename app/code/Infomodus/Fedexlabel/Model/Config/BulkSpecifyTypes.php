<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Fedexlabel\Model\Config;
class BulkSpecifyTypes implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => 'All', 'value' => 'all'],
            ['label' => 'Specify', 'value' => 'specify'],
        ];
    }
}