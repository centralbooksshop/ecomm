<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Model\Config\Source;

class SortDirection implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'ASC', 'label' => __('Ascending')],
            ['value' => 'DESC', 'label' => __('Descending')]
        ];
    }
}
