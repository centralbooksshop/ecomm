<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Fedexlabel\Model\Config;

class PrintType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => __('PDF'), 'value' => 'pdf'],
            ['label' => __('PNG and PDF'), 'value' => 'png'],
            ['label' => __('EPL2'), 'value' => 'EPL2'],
            ['label' => __('ZPLII'), 'value' => 'ZPLII'],
        ];
    }
}
