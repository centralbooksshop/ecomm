<?php
/**
 * Copyright © 2019 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field\Tab;

class Type  extends \Magento\Framework\View\Element\Html\Select implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Type constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'attribute_code', 'label' => __('Product Attribute Code')],
            ['value' => 'static_block', 'label' => __('CMS Static Block')]
        ];
    }

    /**
     * Set element's HTML ID
     *
     * @param string $elementId ID
     * @return $this
     */
    public function setId($elementId)
    {
        $this->setData('id', $elementId);
        return $this;
    }

    /**
     * Set name
     *
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $id = $this->getData('id');
            $this->setId($id);
            $this->setClass('ubcustom-tab-type');
            $attributes = $this->toOptionArray();
            foreach ($attributes as $attribute) {
                $this->addOption($attribute['value'], $attribute['label']);
            }
        }

        return parent::_toHtml();
    }
}