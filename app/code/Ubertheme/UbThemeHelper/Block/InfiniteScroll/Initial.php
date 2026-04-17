<?php
/**
 * Copyright © 2019 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\InfiniteScroll;

use Magento\Framework\View\Element\Template;
use Ubertheme\UbThemeHelper\Model\Config\Source\ScopeOptionsInfinite;
use Magento\Store\Model\ScopeInterface;

class Initial extends Template
{
    const XML_PATH_INFINITE_SCROLL_GENERAL_ENABLE = 'element/main/infinite_scroll/infinite_scroll_general/enable';
    const XML_PATH_INFINITE_SCROLL_GENERAL_SCOPE = 'element/main/infinite_scroll/infinite_scroll_general/scope';
    const XML_PATH_INFINITE_SCROLL_GENERAL_CATEGORIES_IDS = 'element/main/infinite_scroll/infinite_scroll_general/categories_ids';
    const XML_PATH_INFINITE_SCROLL_GENERAL_METHOD = 'element/main/infinite_scroll/infinite_scroll_general/method';
    const XML_PATH_INFINITE_SCROLL_GENERAL_SHOW_PARAM = 'element/main/infinite_scroll/infinite_scroll_general/show_param';
    const XML_PATH_INFINITE_SCROLL_STYLE_LOADING_MORE_TEXT = 'element/main/infinite_scroll/infinite_scroll_style/loading_text';
    const XML_PATH_INFINITE_SCROLL_STYLE_DONE_TEXT = 'element/main/infinite_scroll/infinite_scroll_style/done_text';
    const XML_PATH_INFINITE_SCROLL_STYLE_IMAGE_LOADING = 'element/main/infinite_scroll/infinite_scroll_style/image';
    const XML_PATH_INFINITE_SCROLL_STYLE_BUTTON_TEXT = 'element/main/infinite_scroll/infinite_scroll_style/button';
    const XML_PATH_INFINITE_SCROLL_STYLE_HIDE_TOOLBAR = 'element/main/infinite_scroll/infinite_scroll_style/hide_toolbar';
    const XML_PATH_INFINITE_SCROLL_STYLE_PAGE_LIMIT = 'element/main/infinite_scroll/infinite_scroll_general/number_page_limit';
    const XML_PATH_CATEGORY_PAGE_LIST_MODE = 'catalog/frontend/list_mode';

    /**
     * @var \Ubertheme\UbThemeHelper\App\Config
     */
    protected $themeConfig;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Initial constructor.
     * @param Template\Context $context
     * @param \Ubertheme\UbThemeHelper\App\Config $themeConfig
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ubertheme\UbThemeHelper\App\Config $themeConfig,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->themeConfig = $themeConfig;
        $this->catalogSession = $catalogSession;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isEnable($type = "widget")
    {
        $flag = false;
        $isEnable = $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_GENERAL_ENABLE);
        if (!$isEnable) {
            return false;
        }
        $scopesApply = $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_GENERAL_SCOPE);
        $scopesApplyArr = explode(',', $scopesApply);
        //Check is enable for product list widget
        if ($type == "widget") {
            if (in_array(ScopeOptionsInfinite::TYPE_WIDGET, $scopesApplyArr)) {
                $flag = true;
            }
            return $flag;
        }

        //Check is enable for page
        $fullAction = $this->getRequest()->getFullActionName();
        // If current page is category page
        if ($fullAction == 'catalog_category_view') {
            if (in_array(ScopeOptionsInfinite::TYPE_CATEGORY_PAGE, $scopesApplyArr)) {
                $catId = $this->catalogSession->getData('last_viewed_category_id');
                $category_ids = $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_GENERAL_CATEGORIES_IDS);
                if ($category_ids) {
                    $categoriesIds_arr = explode(',', $category_ids);
                    if (in_array($catId, $categoriesIds_arr)) {
                        $flag = true;
                    }
                } else {
                    $flag = true;
                }
            }
        }
        // If current page is search page
        if ($fullAction == 'catalogsearch_advanced_result' || $fullAction == 'catalogsearch_result_index') {
            if (in_array(ScopeOptionsInfinite::TYPE_SEARCH_PAGE, $scopesApplyArr)) {
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLoaderImageURL()
    {
        $currentStore = $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $img = $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_STYLE_IMAGE_LOADING);
        if ($img) {
            return $mediaUrl . '/ubertheme/ubthemehelper/infiniteScroll/' . $img;
        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getLoadingMode()
    {
        return $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_GENERAL_METHOD);
    }

    /**
     * @return mixed
     */
    public function isShowParam()
    {
        return $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_GENERAL_SHOW_PARAM);
    }

    /**
     * @return mixed
     */
    public function getLoadingMoreText()
    {
        return $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_STYLE_LOADING_MORE_TEXT);
    }

    /**
     * @return mixed
     */
    public function getNoMoreItemsText()
    {
        return $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_STYLE_DONE_TEXT);
    }

    /**
     * @return mixed
     */
    public function getButtonText()
    {
        return $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_STYLE_BUTTON_TEXT);
    }

    /**
     * @return mixed
     */
    public function isShowToolbar()
    {
        return $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_STYLE_HIDE_TOOLBAR);
    }

    /**
     * @return mixed
     */
    public function getNumberPageLimit()
    {
        return $this->themeConfig->getValue(self::XML_PATH_INFINITE_SCROLL_STYLE_PAGE_LIMIT);
    }

    /**
     * @return string
     */
    public function getProductListMode()
    {
        $currentMode = $this->getRequest()->getParam('product_list_mode');
        $mode = ($currentMode) ?
            $currentMode : $this->scopeConfig->getValue(self::XML_PATH_CATEGORY_PAGE_LIST_MODE, ScopeInterface::SCOPE_STORE);

        return substr($mode,0, 4) ;
    }

    /**
     * @return bool
     */
    public function canLoadOnScrolling()
    {
        $flag = true;
        $loadingMode = $this->getLoadingMode();
        if ($loadingMode == 'click' || ($loadingMode == 'scroll_limit' && ($this->getNumberPageLimit() == 1))) {
            $flag =  false;
        }

        return $flag;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isEnable("page")) {
            return '';
        }

        return parent::_toHtml();
    }
}