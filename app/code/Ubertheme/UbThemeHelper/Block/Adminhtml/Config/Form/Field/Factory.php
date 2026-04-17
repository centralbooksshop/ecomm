<?php
/**
 * Copyright © 2016 UberTheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param array $data
     * @return \Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field::class, $data);
    }
}
