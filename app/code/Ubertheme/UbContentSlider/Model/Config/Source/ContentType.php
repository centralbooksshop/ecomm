<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Model\Config\Source;

class ContentType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'latest_products', 'label' => __('Latest Products')],
            ['value' => 'new_products', 'label' => __('New Products (From...To.. Date)')],
            ['value' => 'hot_products', 'label' => __('Hot Products')],
            ['value' => 'bestseller_products', 'label' => __('Bestsellers Products')],
            ['value' => 'random_products', 'label' => __('Show Random Products')],
            ['value' => 'slider', 'label' => __('Slider Uploaded (images/videos)')],
        ];
    }
}
