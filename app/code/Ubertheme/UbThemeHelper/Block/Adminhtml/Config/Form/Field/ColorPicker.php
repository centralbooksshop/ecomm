<?php

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field;

class ColorPicker extends \Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field
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
        $html = $element->addClass('ub-color-picker')->getElementHtml();
        $value = $element->getData('value');

        $html .= '<span class="color-validation"></span>';
        $html .= '<input id="color-picker-'.$element->getHtmlId().'" type="hidden" />';
        $html .= '<script type="text/javascript">
            require(["jquery", "spectrum"], function ($) {
                $(document).ready(function () {
                    var $input = $("#' . $element->getHtmlId() . '");
                    var $picker = $("#color-picker-' . $element->getHtmlId() . '");
                    $picker.spectrum({
                        color: "'. $value .'",
                        showInitial: true,
                        allowEmpty: false,
                        showInput: true,
                        showAlpha: false,
                        showPalette: true,
                        palette: [
                            ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
                            ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
                            ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
                            ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
                            ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
                            ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
                            ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
                            ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
                        ],
                        /*maxPaletteSize: 10,
                        showPaletteOnly: true,
                        togglePaletteOnly: true,*/
                        showSelectionPalette: true,
                        clickoutFiresChange: true,
                        preferredFormat: "hex",
                        className: "ub-color-picker",
                        theme: "sp-light",
                        showButtons: false,
                        cancelText: "'.__("Cancel").'",
                        chooseText: "'.__("Choose").'",
                        noColorSelectedText: "'.__("You need to specify the color code").'",
                        localStorageKey: "ub-colors-field- '.$element->getHtmlId().' ",
                        change: function(color) {
                            $input.val(color.toHexString()).trigger("change");
                        }
                    });
                    $picker.on("dragstop.spectrum", function(e, color) {
                        $input.val(color.toHexString()).trigger("change");
                    });

                    // validate for manual input color code
                    var $btnSave = $("#save");
                    $input.on("change", function() {
                        var val = $(this).val(), msg = "";
                        var regColorCode = /^(#)?([0-9a-fA-F]{3})([0-9a-fA-F]{3})?$/;
                        if(regColorCode.test(val) == false) {
                            $(this).select();
                            $btnSave.addClass("disabled");
                            if (!val.length) {
                                msg = "'.__("You need to specify the color code").'";
                            } else {
                                msg = "'.__("Color code was not matched").'";
                            }
                            if (msg.length) {
                                $(this).parent().addClass("error");
                                $(this).siblings(".color-validation").html(msg);
                            }
                        } else {
                            //clean error
                            $(this).parent().removeClass("error");
                            $(this).siblings(".color-validation").html("");
                            //update preview
                            $picker.siblings(".sp-replacer.ub-color-picker")
                            .find(".sp-preview-inner").css("backgroundColor", val);
                            //enable save button
                            $btnSave.removeClass("disabled");
                        }
                    });
                });
            });
            </script>';
        return $html;
    }
}
