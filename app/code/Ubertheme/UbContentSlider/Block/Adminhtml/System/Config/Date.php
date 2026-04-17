<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Block\Adminhtml\System\Config;
//use Magento\Framework\Data\Form\Element\Date;
class Date extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDateFormat(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat(null);
        return parent::render($element);
    }
}