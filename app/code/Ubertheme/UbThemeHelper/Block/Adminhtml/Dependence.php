<?php
/**
 * Copyright © 2019 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml;

class Dependence extends \Magento\Backend\Block\Widget\Form\Element\Dependence {

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_depends) {
            return '';
        }

        $params = $this->_getDependsJson();
        if ($this->_configOptions) {
            $params .= ', ' .  $this->_jsonEncoder->encode($this->_configOptions);
        }

        return "<script>
                    require(['mage/adminhtml/form'], function(){
                       window.elementsMap = {$params};
                        new FormElementDependenceController({$params});
                    });
                </script>";
    }

}