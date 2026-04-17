<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Fedexlabel\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class SmartPostHubId implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => __('-- Please Select --'), 'value' => ''],
            ['label' => __('ALPA Allentown'), 'value' => '5185'],
            ['label' => __('ATGA Atlanta'), 'value' => '5303'],
            ['label' => __('CHNC Charlotte'), 'value' => '5281'],
            ['label' => __('COCA Chino'), 'value' => '5929'],
            ['label' => __('DLTX Dallas'), 'value' => '5751'],
            ['label' => __('DNCO Denver'), 'value' => '5802'],
            ['label' => __('DTMI Detroit'), 'value' => '5481'],
            ['label' => __('EDNJ Edison'), 'value' => '5087'],
            ['label' => __('GCOH Grove City'), 'value' => '5431'],
            ['label' => __('HOTX Houston'), 'value' => '5771'],
            ['label' => __('GPOH Groveport Ohio'), 'value' => '5436'],
            ['label' => __('LACA Los Angeles'), 'value' => '5902'],
            ['label' => __('ININ Indianapolis'), 'value' => '5465'],
            ['label' => __('KCKS Kansas City'), 'value' => '5648'],
            ['label' => __('MAWV Martinsburg'), 'value' => '5254'],
            ['label' => __('METN Memphis'), 'value' => '5379'],
            ['label' => __('MPMN Minneapolis'), 'value' => '5552'],
            ['label' => __('NBWI New Berlin'), 'value' => '5531'],
            ['label' => __('NENY Newburgh'), 'value' => '5110'],
            ['label' => __('NOMA Northborough'), 'value' => '5015'],
            ['label' => __('ORFL Orlando'), 'value' => '5327'],
            ['label' => __('PHPA Philadelphia'), 'value' => '5194'],
            ['label' => __('PHAZ Phoenix'), 'value' => '5854'],
            ['label' => __('PTPA Pittsburgh'), 'value' => '5150'],
            ['label' => __('SACA Sacramento'), 'value' => '5958'],
            ['label' => __('SCUT Salt Lake City'), 'value' => '5843'],
            ['label' => __('SEWA Seattle'), 'value' => '5983'],
            ['label' => __('STMO St. Louis'), 'value' => '5631'],
            ['label' => __('RENV Reno'), 'value' => '5893'],
        ];
    }
}
