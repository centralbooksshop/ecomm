<?php
/**
 * Copyright © 2016 UberTheme. All rights reserved.
 */
namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

class HeadingFull extends Field
{

    /**
     * render separator config row
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $fieldConfig = $element->getFieldConfig();
        $htmlId = $element->getHtmlId();
        $html = '<tr id="row_' . $htmlId . '">'
        . '<td class="label" colspan="4">';

        if($element->getLabel() != "") {
            $html .= '<div class="ub_heading_full">';
            $html .= '<span>'.$element->getLabel().'</span>';
            $html .= '<div class="comment" style="font-weight: 400;text-align: left;">' . $element->getComment() . '</div>';
            $html .= '</div>';
        }
        
        $html .= '</td></tr>';

        return $html;
    }
}