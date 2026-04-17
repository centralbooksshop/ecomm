<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Fedexlabel\Model\Config;

class PrintFormatPdf implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => '4X6', 'value' => 'PAPER_4X6'],
            ['label' => '4X8', 'value' => 'PAPER_4X8'],
            ['label' => '4X9', 'value' => 'PAPER_4X9'],
            ['label' => '7X4.75', 'value' => 'PAPER_7X4.75'],
            ['label' => '8.5X11_BOTTOM_HALF_LABEL', 'value' => 'PAPER_8.5X11_BOTTOM_HALF_LABEL'],
            ['label' => '8.5X11_TOP_HALF_LABEL', 'value' => 'PAPER_8.5X11_TOP_HALF_LABEL'],
            ['label' => 'LETTER', 'value' => 'PAPER_LETTER'],
        ];
    }
}
