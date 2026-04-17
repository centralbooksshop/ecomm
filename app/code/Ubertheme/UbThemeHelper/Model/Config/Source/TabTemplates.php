<?php
/**
 * Copyright © 2019 Ubertheme. All rights reserved.
 */
namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class TabTemplates implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_TAB = 'tab';
    const TYPE_LIST = 'list';
    const TYPE_ACCORDION = 'accordion';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_ACCORDION, 'label' => __('Accordion')],
            ['value' => self::TYPE_LIST, 'label' => __('List')],
            ['value' => self::TYPE_TAB, 'label' => __('Tab')]
        ];
    }
}
