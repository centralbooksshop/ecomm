<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration structure
 */
namespace Ubertheme\UbThemeHelper\Model\Config;

class Structure extends \Magento\Config\Model\Config\Structure
{
    /**
     * @param Structure\Data $structureData
     * @param \Magento\Config\Model\Config\Structure\Element\Iterator\Tab $tabIterator
     * @param \Magento\Config\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory
     * @param \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
     */
    public function __construct(
        \Ubertheme\UbThemeHelper\Model\Config\Structure\Data $structureData,
        \Magento\Config\Model\Config\Structure\Element\Iterator\Tab $tabIterator,
        \Magento\Config\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory,
        \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
    ) {
        $this->_data = $structureData->get();
        $this->_tabIterator = $tabIterator;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_scopeDefiner = $scopeDefiner;
    }
}
