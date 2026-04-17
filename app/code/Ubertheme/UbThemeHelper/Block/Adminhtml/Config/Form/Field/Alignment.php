<?php

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field;

class Alignment extends \Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->addClass('ub-alignment-field hide')->getElementHtml();
        $options = $this->_getOptions();
        $inputName = 'alignment-option-' . $element->getHtmlId();
        $html .= '<ul id="'. $element->getHtmlId() . '-alignment-options' .'" class="ub-alignment-options">';
        foreach ($options as $val => $label) {
            $cls = $val;
            $checked = '';
            if ($val == $element->getData('value')) {
                $cls .= " selected";
                $checked = 'checked="checked"';
            }
            $html .= '<li class="'. $cls .'" title="' . $label . '">';
            $html .= '<input type="radio" name="' . $inputName . '" '. $checked .' value="' . $val . '" />' . $label;
            $html .= '</li>';
        }
        $html .= '</ul>';

        $html .= '<script type="text/javascript">
            require(["jquery"], function ($) {
                $(document).ready(function () {
                    var $input = $("#' . $element->getHtmlId() . '");
                    var $options = $("#' . $element->getHtmlId() . '-alignment-options li"); 
                    $options.click(function() {
                        $options.removeClass("selected");
                        $(this).addClass("selected");
                        $(this).find("input[type=\'radio\']").prop("checked", true);
                        var selectedVal = $("input[name=\'' . $inputName . '\']:checked").val();
                        $input.val(selectedVal).trigger("change");
                    });
                });
            });
            </script>';

        return $html;
    }

    protected function _getOptions() {
        return [
            'left' => __('Left'),
            'right' => __('Right'),
            'center' => __('Center'),
            'justify' => __('Justify')
        ];
    }

}
