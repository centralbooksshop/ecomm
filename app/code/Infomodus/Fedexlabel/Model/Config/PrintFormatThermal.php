<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Fedexlabel\Model\Config;

class PrintFormatThermal implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => '4X6', 'value' => 'STOCK_4X6'],
            ['label' => '4X6.75_LEADING_DOC_TAB', 'value' => 'STOCK_4X6.75_LEADING_DOC_TAB'],
            ['label' => '4X6.75_TRAILING_DOC_TAB', 'value' => 'STOCK_4X6.75_TRAILING_DOC_TAB'],
            ['label' => '4X8', 'value' => 'STOCK_4X8'],
            ['label' => '4X9_LEADING_DOC_TAB', 'value' => 'STOCK_4X9_LEADING_DOC_TAB'],
            ['label' => '4X9_TRAILING_DOC_TAB', 'value' => 'STOCK_4X9_TRAILING_DOC_TAB'],
        ];
    }
}
