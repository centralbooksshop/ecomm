<?php

/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */

namespace Ubertheme\UbContentSlider\Block;

use Ubertheme\UbContentSlider\Model\Item\Image as ImageModel;

class Slider extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Framework\DataObject\IdentityInterface {

    /**
     * @var \Magento\Catalog\Block\Product\Context $context
     */
    protected $_context;

    /**
     * @var \Ubertheme\UbContentSlider\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_urlHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var ImageModel
     */
    protected $_imageModel;

    protected $_configs = [
        "enable" => true,
        "show_title" => null,
        "title" => null,
        "content_type" => null,
        "slider_id" => null,
        "slider_key" => null,
        "category_ids" => null,
        "sort_by" => null,
        "sort_dir" => null,
        "from_date" => null,
        "to_date" => null,
        "qty" => null,
        "item_width" => null,
        "item_height" => null,
        "show_item_title" => null,
        "show_item_desc" => null,
        "thumb_width" => null,
        "thumb_height" => null,
        "show_name" => null,
        "show_price" => null,
        "show_desc" => null,
        "desc_length" => null,
        "show_review" => null,
        "show_wishlist" => null,
        "show_compare" => null,
        "show_add_cart" => null,
        "show_readmore" => null,
        "js_lib" => null,
        "single_item" => null,
        "number_items" => null,
        "number_items_desktop" => null,
        "number_items_desktop_small" => null,
        "number_items_tablet" => null,
        "number_items_tablet_small" => null,
        "number_items_mobile" => null,
        "auto_run" => null,
        "auto_height" => null,
        "slide_speed" => null,
        "stop_on_hover" => null,
        "show_navigation" => null,
        "navigation_text" => null,
        "show_paging" => null,
        "paging_numbers" => null,
        "enable_lazyload" => null,
        "show_thumbnail" => null,
        "slide_transition" => null,
        "show_processbar" => null,
        "addition_class"=> null,
        "template" => '',
    ];

    /**
     * Slider constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Ubertheme\UbContentSlider\Helper\Data $dataHelper
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param ImageModel $imageModel
     * @param array $data
     */
    public function __construct(
    \Magento\Catalog\Block\Product\Context $context,
    \Magento\Framework\Json\Helper\Data $jsonHelper,
    \Ubertheme\UbContentSlider\Helper\Data $dataHelper,
    \Magento\Framework\Url\Helper\Data $urlHelper,
    ImageModel $imageModel,
    array $data = []
    ) {
        $this->_context = $context;
        $this->_jsonHelper = $jsonHelper;
        $this->_dataHelper = $dataHelper;
        $this->_urlHelper = $urlHelper;
        $this->_imageModel = $imageModel;

        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {
        $this->processConfigs($this->getData());

        if (!$this->_configs->enable) {
            return '';
        }

        //if has custom template
        if (!empty($this->_configs->template)) {
            $this->setTemplate($this->_configs->template);
        }

        //use default
        if (!$this->getTemplate()) {
            if ($this->_configs->js_lib == 'owl1') {
                if ($this->_configs->content_type == 'slider') {
                    $tmpl = "Ubertheme_UbContentSlider::{$this->_configs->js_lib}_slider.phtml";
                } else {
                    $tmpl = "Ubertheme_UbContentSlider::{$this->_configs->js_lib}_slider_products.phtml";
                }
            } else {
                return '';
            }
            $this->setTemplate($tmpl);
        }

        //assign needed data to template
        $this->assign('config', $this->_configs);
        $this->assign('items', $this->_dataHelper->getSliderItems($this->_configs));

        return $this->fetchView($this->getTemplateFile());
    }
    
    protected function processConfigs($data) {
        foreach ($this->_configs as $key => $val) {
            $this->_configs[$key] = $this->_dataHelper->getConfigValue($key, $data);
        }
        $this->_configs['navigation_text'] = [__('Prev'), __('Next')];
        $this->_configs['attributesToSelect'] = $this->_context->getCatalogConfig()->getProductAttributes();

        //convert to object
        $this->_configs = (object) $this->_configs;

        return $this->_configs;
    }

    public function getOwl1JsonConfigs() {
        return $this->_jsonHelper->jsonEncode($this->_configs);
    }

    public function getSliderImageUrl($image){
        return $this->_imageModel->getBaseUrl().$image;
    }

    public function getIdentities() {
        return [
            \Magento\Store\Model\Store::CACHE_TAG,
            \Ubertheme\UbContentSlider\Model\Slide::CACHE_TAG,
            \Ubertheme\UbContentSlider\Model\Item::CACHE_TAG
        ];
    }

    public function loadProductById($productId) {
        $rs = null;
        if ($productId) {
            /** @var \Magento\Catalog\Model\Product $product */
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $om->get('\Magento\Catalog\Model\Product');
            $rs = $product->load($productId);
        }

        return $rs;
    }
}
