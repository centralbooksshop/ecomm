<?php

namespace Centralbooks\ClickpostExtension\Model\Awb\Source;

class State implements \Magento\Framework\Option\ArrayInterface
{
    const USED = 1;
    const UNUSED = 2;
	const EXPIRED = 4;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::USED,
                'label' => __('Used')
            ],
            [
                'value' => self::UNUSED,
                'label' => __('Unused')
            ],
			[
                'value' => self::EXPIRED,
                'label' => __('Expired')
            ],
        ];
        return $options;

    }
}
