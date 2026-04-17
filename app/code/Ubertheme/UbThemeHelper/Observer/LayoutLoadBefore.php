<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Observer;

class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    const CATEGORY_LAYOUT_CONFIG_PATH = 'category_view/general/layout';
    const PRODUCT_LAYOUT_CONFIG_PATH = 'product_view/general/layout';
    const CONTACT_LAYOUT_CONFIG_PATH = 'contact_view/general/layout';

    /**
     * @var \\Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Ubertheme\UbThemeHelper\App\Config
     */
    protected $_themeConfig;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Ubertheme\UbThemeHelper\App\Config $themeConfig
    )
    {
        $this->_request = $request;
        $this->_themeConfig = $themeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $configPath = null;
        $actionName = $this->_request->getFullActionName();
        if ($actionName == 'catalog_category_view') {
            $configPath = self::CATEGORY_LAYOUT_CONFIG_PATH;
        } elseif ($actionName == 'catalog_product_view') {
            $configPath = self::PRODUCT_LAYOUT_CONFIG_PATH;
        } elseif ($actionName == 'contact_index_index') {
            $configPath = self::CONTACT_LAYOUT_CONFIG_PATH;
        } else {
            return $this;
        }

        if ($configPath) {
            $customLayout = $this->getThemeConfig($configPath);
            /** @var \Magento\Framework\View\LayoutInterface $layout */
            $layout = $observer->getLayout();
            $layout->getUpdate()->addHandle($customLayout);
        }

        return $this;
    }

    protected function getThemeConfig($fullPath)
    {
        return $this->_themeConfig->getValue($fullPath);
    }
}