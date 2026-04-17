<?php
/**
 * Copyright © 2016 UberTheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Ubertheme\UbThemeHelper\Helper\Data;

class CustomNotes extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td style="width: 100%;">';
        $html .= $this->_getElementHtml($element);

        if ($element->getComment()) {
            $html .= '<p class="section-note"><span>' . $element->getComment() . '</span></p>';
        }

        //trigger to show this group
        $elementId = $element->getData('container')->getId();
        $headId = $elementId.'-head';
        $stateId = $elementId.'-state';
        $html .= '<script type="text/javascript">
        //<![CDATA[
            require(["prototype"], function() {
              if($("'.$headId.'") != undefined ) {
                 $("'.$headId.'").hide();
                 if (!$("'.$headId.'").hasClassName("open")) {
                    $("'.$headId.'").fire("click");
                    //update opened state
                    if($("'.$stateId.'") != undefined ) {
                        $("' . $stateId . '").setAttribute("value", 1);
                    }
                 }
                }
            });
        //]]>
        </script>';

        $html .= '</td>';
        return $html;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_renderValue($element);
        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

}