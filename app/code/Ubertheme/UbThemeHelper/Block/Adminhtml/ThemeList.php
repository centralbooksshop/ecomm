<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml;

use Magento\Framework\App\Area;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThemeList extends \Magento\Backend\Block\Template
{
    const THEME_CONFIG_PATH = 'design/theme/theme_id';

    /* @var \Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory $themeCollectionFactory */
    protected $themeCollectionFactory;

    protected $activatedThemeId;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Model\Menu\Config $menuConfig,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory $themeCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_url = $url;
        $this->_iteratorFactory = $iteratorFactory;
        $this->_authSession = $authSession;
        $this->_menuConfig = $menuConfig;
        $this->_localeResolver = $localeResolver;
        $this->_objectManager = $objectManager;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->activatedThemeId = null;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        /** @var \Magento\Config\Model\Config $config */
        $config = $this->_objectManager->create('Magento\Config\Model\Config');
        $this->session = 'design';
        $this->websiteId = $this->getRequest()->getParam('website');
        $this->storeId = $this->getRequest()->getParam('store');
        $this->vendor = $this->getRequest()->getParam('vendor');
        $config->setData([
            'session' => $this->session,
            'website' => $this->websiteId,
            'store' => $this->storeId
        ]);

        if ($this->storeId) {
            /**
             * check has setting schedule switch theme by store in design_change table
             */
            /* @var \Magento\Theme\Model\ResourceModel\Design\Collection $collection */
            $collection = $this->_objectManager->create('\Magento\Theme\Model\ResourceModel\Design\Collection');
            $collection->addFieldToFilter('store_id', $this->storeId);
            $collection->addDateFilter();

            /** @var \Magento\Theme\Model\Design $design */
            $design = $collection->getFirstItem();
            $this->activatedThemeId = $design->getDesign();
        }

        if (!$this->activatedThemeId) {
            //get design setting from system config table
            $this->activatedThemeId = $config->getConfigDataValue(self::THEME_CONFIG_PATH);
        }
    }

    public function getThemes()
    {
        $collection = $this->themeCollectionFactory->create();
        $collection->addOrder('main_table.code', 'ASC');
        return $collection;
    }

    public function getActivatedThemeId()
    {
        return $this->activatedThemeId;
    }

    public function getThemePreReviewImageUrl($theme)
    {
        $themeInterface = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        $themeInterface->load($theme->getThemeId());
        return $themeInterface->getThemeImage()->getPreviewImageUrl();
    }

    public function getActiveThemeUrl($themeId)
    {
        $params = [];
        $params['themeId'] = $themeId;
        $params['vendor'] = $this->vendor;
        if ($this->websiteId) {
            $params['website'] = $this->websiteId;
        }
        if ($this->storeId) {
            $params['store'] = $this->storeId;
        }

        return $this->getUrl('ubthemehelper/theme/active', $params);
    }

    public function getSettingUrl($theme)
    {
        $url = null;
        if (self::isMatched($theme, 'ubertheme')) {
            $params = array();
            $params['themeId'] = $theme->getId();
            $params['vendor'] = $this->vendor;
            $params['section'] = 'overview';
            $url = $this->getUrl('ubthemehelper/config/edit', $params);
        }

        return $url;
    }

    public function getThemeVersion($themePath)
    {
        $version = __('Unknown');
        $themePath = str_replace('_', '/', $themePath);
        $rootPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
        $pathToComposerFile = $rootPath . '/app/design/frontend/' . $themePath . '/composer.json';

        if (file_exists($pathToComposerFile)) {
            $defined = json_decode(file_get_contents($pathToComposerFile));
            if (isset ($defined->version)) {
                $version = $defined->version;
            }
        }

        return $version;
    }

    public static function isMatched($theme, $vendor) {
        $rs = false;
        $codes = explode("/", strtolower($theme->getCode()));
        if (isset($codes[0]) && $codes[0] == strtolower($vendor)) {
            $rs = true;
        }

        return $rs;
    }
}
