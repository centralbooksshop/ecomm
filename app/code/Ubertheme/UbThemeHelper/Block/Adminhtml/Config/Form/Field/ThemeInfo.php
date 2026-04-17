<?php
/**
 * Copyright © 2016 UberTheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Ubertheme\UbThemeHelper\Helper\Data;

class ThemeInfo extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Ubertheme_UbThemeHelper::system/config/form/field/theme.info.phtml';

    protected $helper;

    protected $objectManager;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->objectManager = $objectManager;
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
        /*if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value">';
            $html .= $this->_getElementHtml($element);
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }*/

        $html = '<td>';
        $html .= $this->_getElementHtml($element);
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

    public function getCurrentTheme()
    {
        $themeId = $this->helper->getRequest()->getParam('themeId');
        /** @var \Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory $themeCollectionFactory */
        $themeCollectionFactory = $this->objectManager->create('\Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory');
        $collection = $themeCollectionFactory->create();
        $collection->addFieldToFilter('main_table.theme_id', array('eq' => $themeId));

        return $collection->getFirstItem();
    }

    public function getThemePreReviewImageUrl($theme)
    {
        $themeInterface = $this->objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        $themeInterface->load($theme->getThemeId());

        return $themeInterface->getThemeImage()->getPreviewImageUrl();
    }

    public function getComposerInfo($key, $themePath) {
        $rs = __('N/A');
        $rootPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))))));
        $pathToComposerFile = $rootPath.'/app/design/frontend/'.$themePath.'/composer.json';
        if (file_exists($pathToComposerFile)) {
            $defined = json_decode(file_get_contents($pathToComposerFile));
            if (isset ($defined->$key)) {
                $rs = $defined->$key;
            }
        }

        return $rs;
    }
}