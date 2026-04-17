<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
class ReasonForExport implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('COURTESY RETURN LABEL'), 'value' => 'COURTESY_RETURN_LABEL'],
            ['label' => __('EXHIBITION TRADE SHOW'), 'value' => 'EXHIBITION_TRADE_SHOW'],
            ['label' => __('FAULTY ITEM'), 'value' => 'FAULTY_ITEM'],
            ['label' => __('FOLLOWING REPAIR'), 'value' => 'FOLLOWING_REPAIR'],
            ['label' => __('FOR REPAIR'), 'value' => 'FOR_REPAIR'],
            ['label' => __('ITEM FOR LOAN'), 'value' => 'ITEM_FOR_LOAN'],
            ['label' => __('OTHER'), 'value' => 'OTHER'],
            ['label' => __('REJECTED'), 'value' => 'REJECTED'],
            ['label' => __('REPLACEMENT'), 'value' => 'REPLACEMENT'],
            ['label' => __('TRIAL'), 'value' => 'TRIAL'],
        ];
        return $c;
    }
}