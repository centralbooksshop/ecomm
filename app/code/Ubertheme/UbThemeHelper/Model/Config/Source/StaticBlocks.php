<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class StaticBlocks implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Ubertheme\UbThemeHelper\Helper\Data $helper */
        $helper = $om->get('Ubertheme\UbThemeHelper\Helper\Data');
        $blocks = $helper->getStaticBlockOptions();
        $options = [];
        foreach ($blocks as $blockIdentify => $blockName) {
            $options[] = [
                'value' => $blockIdentify,
                'label' => $blockName
            ];
        }

        return $options;
    }
    public function toArray()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Ubertheme\UbThemeHelper\Helper\Data $helper */
        $helper = $om->get('Ubertheme\UbThemeHelper\Helper\Data');
        $blocks = $helper->getStaticBlockOptions();
        $options = [];
        foreach ($blocks as $blockIdentify => $blockName) {
            $options[$blockIdentify] = $blockName;
        }

        return $options;
    }
}
