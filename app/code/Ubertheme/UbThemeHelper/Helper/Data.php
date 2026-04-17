<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Helper;

use Magento\Store\Model\Store;
use Ubertheme\Base\Helper\MobileDetect;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const THEME_CONFIG_PATH = 'design/theme/theme_id';

    /** @var \Magento\Store\Model\StoreManagerInterface $_storeManager */
    protected $_storeManager;

    /** @var \Magento\Framework\View\Page\Config $_pageConfig */
    protected $_pageConfig;

    /** @var \Ubertheme\UbThemeHelper\App\Config $_themeConfig */
    protected $_themeConfig;

    /** @var \Ubertheme\UbThemeHelper\Model\ConfigFactory $_configFactory */
    protected $_configFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieMetadataManager;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList */
    protected $_directoryList;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ubertheme\UbThemeHelper\App\Config $themeConfig
     * @param \Ubertheme\UbThemeHelper\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\App\Http\Context $httpContext
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ubertheme\UbThemeHelper\App\Config $themeConfig,
        \Ubertheme\UbThemeHelper\Model\ConfigFactory $configFactory,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Http\Context $httpContext
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_themeConfig = $themeConfig;
        $this->_pageConfig = $pageConfig;
        $this->_configFactory = $configFactory;
        $this->_directoryList = $directoryList;
        $this->httpContext = $httpContext;
    }

    /**
     * @param $fullPath
     * @return mixed
     */
    public function getParam($fullPath)
    {
        return $this->_themeConfig->getValue($fullPath);
    }

    /**
     * @param string $type
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl($type = null)
    {
        if ($type) {
            $baseURL = $this->_storeManager->getStore()->getBaseUrl($type);
        } else {
            $baseURL = $this->_storeManager->getStore()->getBaseUrl();
        }

        return $baseURL;
    }
    public function getWebsiteId(){
        return $this->_storeManager->getStore()->getWebsiteId();
    }
    /**
     * @return string
     */
    public function isMobile()
    {
        return $this->getDevice() == 'mobile' ? true : false;
    }

    /**
     * @return string
     */
    public function getDevice()
    {
        /** @var \Ubertheme\Base\Helper\MobileDetect $detect */
        $detect = \Magento\Framework\App\ObjectManager::getInstance()->create('Ubertheme\Base\Helper\MobileDetect');

        if ($detect->isMobile()) {
            if ($detect->isTablet()) {
                // Any tablet device.
                return 'tablet';
            } else {
                // Exclude tablets.
                return 'mobile';
            }
        } else {
            return 'desktop';
        }
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return string
     */
    public function getPageConfig()
    {
        return $this->_pageConfig->getPageLayout();
    }

    /**
     * @param array $storeIds
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStaticBlockOptions($storeIds = [])
    {
        $options = [];

        if (!$storeIds) {
            $storeIds[] = $this->getStore()->getId();
        }

        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Cms\Model\BlockFactory $blockFactory */
        $blockFactory = $om->get('Magento\Cms\Model\BlockFactory');
        $collection = $blockFactory->create()->getCollection()
            ->addFieldToSelect(['block_id', 'identifier', 'title'])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($collection->getItems() as $item) {
            $options[$item->getIdentifier()] = $item->getTitle();
        }

        return $options;
    }

    /**
     * @param null $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = (int)$this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        }

        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $om->get('\Magento\Backend\App\Action\Context');
        return $context->getRequest();
    }

    /**
     * @return |null
     */
    public static function getCurrentThemeId()
    {
        $themeId = null;
        if (isset($_SERVER['REQUEST_URI'])) {
            $params = explode('/', $_SERVER['REQUEST_URI']);
            for ($i = 0, $n = count($params); $i < $n; $i++) {
                if ($params[$i] == 'themeId') {
                    $themeId = $params[$i + 1];
                    break;
                }
            }
        }

        return $themeId;
    }

    /**
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager|mixed
     */
    public function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|mixed
     */
    public function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getRealPathOfCurrentTheme() {
        $path = null;
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
        $scopeConfig = $om->get("Magento\Framework\App\Config\ScopeConfigInterface");
        $currentThemeId = $scopeConfig->getValue(self::THEME_CONFIG_PATH);

        /** @var \Magento\Theme\Model\Theme $theme */
        $theme = self::getThemeById($currentThemeId);

        if ($theme) {
            //$themePath = str_replace('_', '/', $theme->getThemePath());
            $appPath = $this->_directoryList->getPath('app');
            $path = "{$appPath}/design/frontend/{$theme->getThemePath()}";
        }

        return $path;
    }

    /**
     * @param $themeId
     * @return \Magento\Theme\Model\Theme|null
     */
    public static function getThemeById($themeId) {
        $theme = null;
        if ($themeId) {
            /** @var \Magento\Framework\App\ObjectManager $om */
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Theme\Model\Theme $theme */
            $theme = $om->get('Magento\Theme\Model\Theme')->load($themeId);
        }

        return $theme;
    }

    /**
     * @param $identifier
     * @return string|null
     */
    public function getBlockHTML($identifier) {
        $html = null;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $layout = $om->get('\Magento\Framework\View\LayoutInterface');
        /** @var \Magento\Cms\Block\Block $block */
        $block = $layout->createBlock('Magento\Cms\Block\Block')->setBlockId($identifier);
        if ($block) {
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * Get option text of a attribute in a product
     */
    public function getProductOptionTextByAttribute($product, $attributeCode) {
        $optionText = null;
        if ($product && $attributeCode) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $productRepo = $om->get('\Magento\Catalog\Model\ProductRepository')->getById($product->getId());
            $optionId = $productRepo->getData($attributeCode);
            if ($optionId) {
                /** @var \Magento\Eav\Model\Entity\Attribute $attr */
                $attribute = $product->getResource()->getAttribute($attributeCode);
                if ($attribute->usesSource()) {
                    $optionText = $attribute->getSource()->getOptionText($optionId);
                }
            }
        }

        return $optionText;
    }

    /**
     * Calculate discount percent of a product
     */
    public function getDiscountPercent($product) {
        $discountPercent = null;
        $specialPrice = $product->getFinalPrice();
        $originalPrice = $product->getPrice();
        // Get the Special Price FROM date
        $specialPriceFromDate = $product->getSpecialFromDate();
        // Get the Special Price TO date
        $specialPriceToDate = $product->getSpecialToDate();
        if ($specialPrice < $originalPrice) {
            $today =  time();
            if($today >= strtotime( $specialPriceFromDate) && $today <= strtotime($specialPriceToDate)
                || $today >= strtotime( $specialPriceFromDate) && is_null($specialPriceToDate)) {
                $discountPercent = 100 - round(($specialPrice/$originalPrice) * 100);
            }
        }

        return $discountPercent;
    }

    /**
     * Check a product as new
     */
    public function asNewProduct($product) {
        $rs = false;
        $now = date("Y-m-d");
        $newsFrom = substr($product->getData('news_from_date'), 0, 10);
        $newsTo =  substr($product->getData('news_to_date'), 0, 10);
        if ($newsTo != '' || $newsFrom != '') {
            if (($newsTo != '' && $newsFrom != '' && $now >= $newsFrom && $now <= $newsTo)
                || ($newsTo == '' && $now >= $newsFrom)
                || ($newsFrom == '' && $now <= $newsTo)) {
                $rs = true;
            }
        }

        return $rs;
    }

}
