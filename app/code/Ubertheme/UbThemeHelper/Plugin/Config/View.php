<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Plugin\Config;

class View
{
    const XML_PATH_CATEGORY_VIEW_GRID_IMG_WIDTH = 'category_view/grid/img_width';
    const XML_PATH_CATEGORY_VIEW_GRID_IMG_HEIGHT = 'category_view/grid/img_height';

    const XML_PATH_CATEGORY_VIEW_LIST_IMG_WIDTH = 'category_view/list/img_width';
    const XML_PATH_CATEGORY_VIEW_LIST_IMG_HEIGHT = 'category_view/list/img_height';

    const XML_PATH_PRODUCT_VIEW_IMG_MEDIUM_WIDTH = 'product_view/gallery/general/image_medium_width';
    const XML_PATH_PRODUCT_VIEW_IMG_MEDIUM_HEIGHT = 'product_view/gallery/general/image_medium_height';

    const XML_PATH_PRODUCT_VIEW_IMG_SMALL_WIDTH = 'product_view/gallery/general/image_small_width';
    const XML_PATH_PRODUCT_VIEW_IMG_SMALL_HEIGHT = 'product_view/gallery/general/image_small_height';

    const XML_PATH_PRODUCT_VIEW_RELATED_IMG_WIDTH = 'product_view/related/image_width';
    const XML_PATH_PRODUCT_VIEW_RELATED_IMG_HEIGHT = 'product_view/related/image_height';

    const XML_PATH_PRODUCT_VIEW_UPSELL_IMG_WIDTH = 'product_view/upsell/image_width';
    const XML_PATH_PRODUCT_VIEW_UPSELL_IMG_HEIGHT = 'product_view/upsell/image_height';

    const XML_PATH_CART_VIEW_CROSS_SELL_IMG_WIDTH = 'cart/crosssell/image_width';
    const XML_PATH_CART_VIEW_CROSS_SELL_IMG_HEIGHT = 'cart/crosssell/image_height';

    /**
     * @var \Ubertheme\UbThemeHelper\App\Config
     */
    protected $_themeConfig;

    /**
     * View constructor.
     * @param \Ubertheme\UbThemeHelper\App\Config $themeConfig
     */
    public function __construct(
        \Ubertheme\UbThemeHelper\App\Config $themeConfig
    )
    {
        $this->_themeConfig = $themeConfig;
    }

    /**
     * @param \Magento\Framework\Config\View $subject
     * @param \Closure $proceed
     * @param string $module
     * @param string $mediaType
     * @param string $mediaId
     * @return array
     */
    public function aroundGetMediaAttributes(
        \Magento\Framework\Config\View $subject,
        \Closure $proceed,
        $module,
        $mediaType,
        $mediaId)
    {
        $result = $proceed($module, $mediaType, $mediaId);

        switch ($mediaId) {
            // Category View
            case "category_page_grid":
            case "category_page_grid_hover":
                $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_GRID_IMG_WIDTH));
                $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_GRID_IMG_HEIGHT));
                break;
            case "category_page_list":
            case "category_page_list_hover":
                $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_LIST_IMG_WIDTH));
                $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_LIST_IMG_HEIGHT));
                break;
            // Product View
            case "product_page_image_medium":
                $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_MEDIUM_WIDTH));
                $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_MEDIUM_HEIGHT));
                break;
            case "product_page_image_small":
                $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_SMALL_WIDTH));
                $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_SMALL_HEIGHT));
                break;
            case "related_products_list":
                $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_RELATED_IMG_WIDTH));
                $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_RELATED_IMG_HEIGHT));
                break;
            case "upsell_products_list":
                $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_UPSELL_IMG_WIDTH));
                $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_UPSELL_IMG_HEIGHT));
                break;
            case "cart_cross_sell_products":
                $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CART_VIEW_CROSS_SELL_IMG_WIDTH));
                $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CART_VIEW_CROSS_SELL_IMG_HEIGHT));
                break;
        }

        if ($mediaId == 'category_page_grid_hover' || $mediaId == 'category_page_list_hover') {
            $result['type'] = 'ub_hover_image';
        }

        if (isset($imgWidth) && !empty($imgWidth)) {
            $result['width'] = $imgWidth;
        }
        if (isset($imgHeight) && !empty($imgHeight)) {
            $result['height'] = $imgHeight;
        }

        return $result;
    }

    /**
     * @param \Magento\Framework\Config\View $subject
     * @param array $result
     * @param string $module
     * @param string $mediaType
     * @return array
     */
    public function afterGetMediaEntities(\Magento\Framework\Config\View $subject, array $result, $module, $mediaType)
    {
        foreach ($result as $mediaId => &$options) {

            $imgWidth = null;
            $imgHeight = null;
            switch ($mediaId) {
                // Category View
                case "category_page_grid":
                case "category_page_grid_hover":
                    $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_GRID_IMG_WIDTH));
                    $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_GRID_IMG_HEIGHT));
                    break;
                case "category_page_list":
                case "category_page_list_hover":
                    $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_LIST_IMG_WIDTH));
                    $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CATEGORY_VIEW_LIST_IMG_HEIGHT));
                    break;
                // Product View
                case "product_page_image_medium":
                    $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_MEDIUM_WIDTH));
                    $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_MEDIUM_HEIGHT));
                    break;
                case "product_page_image_small":
                    $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_SMALL_WIDTH));
                    $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_IMG_SMALL_HEIGHT));
                    break;
                case "related_products_list":
                    $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_RELATED_IMG_WIDTH));
                    $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_RELATED_IMG_HEIGHT));
                    break;
                case "upsell_products_list":
                    $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_UPSELL_IMG_WIDTH));
                    $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_PRODUCT_VIEW_UPSELL_IMG_HEIGHT));
                    break;
                case "cart_cross_sell_products":
                    $imgWidth = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CART_VIEW_CROSS_SELL_IMG_WIDTH));
                    $imgHeight = (int)trim($this->_themeConfig->getValue(self::XML_PATH_CART_VIEW_CROSS_SELL_IMG_HEIGHT));
                    break;
            }

            if ($mediaId == 'category_page_grid_hover' || $mediaId == 'category_page_list_hover') {
                $options['type'] = 'ub_hover_image';
            }

            if (!is_null($imgWidth)) {
                $options['width'] = $imgWidth;
            }
            if (!is_null($imgHeight)) {
                $options['height'] = $imgHeight;
            }
        }

        return $result;
    }
}