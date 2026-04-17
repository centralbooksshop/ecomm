<?php

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field;

class ColorPicker1 extends \Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field
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
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<span class="color-code-status"></span>';
        $html .= '<span id="color-prev-'.$element->getHtmlId().'" class="color-prev"></span>';
        $html .= '<script type="text/javascript">
            require(["jquery", "colorpicker"], function ($) {
                $(document).ready(function () {
                    $("#color-prev-'.$element->getHtmlId().'").css("backgroundColor", "' . $value . '");
                    var $el = $("#' . $element->getHtmlId() . '");
                    // Attach the color picker
                    $el.ColorPicker({
                        color: "' . $value . '",
                        onChange: function (hsb, hex, rgb) {
                            $el.val("#" + hex);
                            $("#color-prev-'.$element->getHtmlId().'").css("backgroundColor", "#" + hex);
                            $el.trigger("change");
                        }
                    });
                    // validate input color code
                    var $btnSave = $("#save");
                    $el.on("blur", function() {
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
                                $(this).siblings(".color-code-status").html(msg);
                            }
                        } else {
                            $(this).parent().removeClass("error");
                            $(this).siblings(".color-code-status").html("");
                            $btnSave.removeClass("disabled");
                        }
                    });
                    // trigger to show color picker when click to the preview element
                    $("#color-prev-'.$element->getHtmlId().'").click(function() {
                        $el.click();
                    });
                });
            });
            </script>';
        return $html;
    }
}
