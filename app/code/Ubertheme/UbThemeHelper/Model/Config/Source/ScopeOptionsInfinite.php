<?php
/**
 * Copyright © 2019 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbThemeHelper\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ScopeOptionsInfinite implements ArrayInterface
{
    const TYPE_CATEGORY_PAGE = 'category_page';
    const TYPE_SEARCH_PAGE = 'search_page';
    const TYPE_WIDGET = 'widget';
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_CATEGORY_PAGE , 'label' => __('Category Page')],
            ['value' => self::TYPE_SEARCH_PAGE , 'label' => __('Search Page')],
            ['value' => self::TYPE_WIDGET , 'label' => __('Catalog Products List Widget')]
        ];
    }
}
