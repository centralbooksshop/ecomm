<?php
/**
 * My own options
 *
 */
namespace Shipsy\EcommerceExtension\Model\Config\Source;
class SyncOrderLimitOption
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '5', 'label' => __('5')],
            ['value' => '10', 'label' => __('10')],
            ['value' => '15', 'label' => __('15')]
        ];
    }
}

?>