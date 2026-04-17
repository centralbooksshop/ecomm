<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class TermsOfSale implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('Not use'), 'value' => ''],
            ['label' => __('CFR_OR_CPT'), 'value' => 'CFR_OR_CPT'],
            ['label' => __('CIF_OR_CIP'), 'value' => 'CIF_OR_CIP'],
            ['label' => __('DDP'), 'value' => 'DDP'],
            ['label' => __('DDU'), 'value' => 'DDU'],
            ['label' => __('DAP'), 'value' => 'DAP'],
            ['label' => __('DAT'), 'value' => 'DAT'],
            ['label' => __('EXW'), 'value' => 'EXW'],
            ['label' => __('FOB_OR_FCA'), 'value' => 'FOB_OR_FCA'],
        ];
        return $c;
    }
}