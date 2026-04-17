<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form;

/**
 * Render field html element in Stores Configuration
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
class Field extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        // Disable element if value is inherited from other scope. Flag has to be set before the value is rendered.
        /*if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }*/

        $html = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td>';
        $html .= $this->_renderValue($element);

        if ($isCheckboxRequired) {
            //$html .= $this->_renderInheritCheckbox($element);
            $html .= $this->_renderResetButton($element);
        }

        //$html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $dataConfig = $element->getData('field_config');
        $defaultValue = (isset($dataConfig['default_value']) ? $dataConfig['default_value'] : '');
        $element->addClass('current-value-field');
        if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value ub-field-value">';
            $html .= $this->_getElementHtml($element);
            //add more custom input to storage default value
            $html .= '<input id="'. $element->getHtmlId() . '_default" type="hidden" value="'.$defaultValue.'" class="default-value-field" />';
        }
        if ($element->getHint()) {
            $html .= '<span class="ub-hint"><span>' . $element->getHint() . '</span></span>';
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';
        return $html;
    }

    /**
     * Render reset button which allow reset to default value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderResetButton(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $htmlId = $element->getHtmlId();
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
        $checkedHtml = $element->getInherit() == 1 ? 'checked="checked"' : '';

        $html = '<td class="use-default">';

        $class = !$element->getInherit() ? 'admin__field-fallback-reset btn-reset show'
            : 'admin__field-fallback-reset btn-reset hide';

        $html .= '<button id="'.$htmlId.'_inherit" class="' . $class . '" type="button"><span>';
        $html .=  __('Use default value') . '</span></button>';

        $html .= '<input id="' . $htmlId . '_inherit_checkbox" name="' . $namePrefix . '[inherit]" type="checkbox" value="1"' .
            ' class="checkbox config-inherit hide" ' . $checkedHtml . ' />';

        $html .= '</td>';

        return $html;
    }

}
