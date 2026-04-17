<?php
/**
 * Copyright © 2015 Ubertheme. All rights reserved.

 */

namespace Ubertheme\UbContentSlider\Block;

class Init extends \Magento\Framework\View\Element\Template
{

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ubertheme\UbContentSlider\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ubertheme\UbContentSlider\Helper\Data $dataHelper,
        array $data = []
    )
    {
        //add needed assets
        $pageConfig = $context->getPageConfig();
        if ($dataHelper->getConfigValue('enable', $data)) {
            if ($dataHelper->getConfigValue('js_lib', $data) == 'owl1') {
                $pageConfig->addPageAsset('Ubertheme_UbContentSlider::css/owl-carousel1/owl.carousel.css');
                $pageConfig->addPageAsset('Ubertheme_UbContentSlider::css/owl-carousel1/owl.theme.css');
                $pageConfig->addPageAsset('Ubertheme_UbContentSlider::css/owl-carousel1/owl.transitions.css');
            }
            $pageConfig->addPageAsset('Ubertheme_UbContentSlider::css/module.css');
        }

        parent::__construct($context);
    }
}
