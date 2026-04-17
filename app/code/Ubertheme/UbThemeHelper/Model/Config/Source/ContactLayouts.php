<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */
namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class ContactLayouts extends LayoutOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return self::buildOptions(get_class($this));
    }
}
