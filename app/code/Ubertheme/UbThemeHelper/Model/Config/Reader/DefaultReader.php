<?php
/**
 * Default configuration reader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Reader;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

class DefaultReader implements \Magento\Framework\App\Config\Scope\ReaderInterface
{
    /**
     * @var \Ubertheme\UbThemeHelper\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\Framework\App\Config\Scope\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $themeId;

    /**
     * DefaultReader constructor.
     * @param \Ubertheme\UbThemeHelper\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Framework\App\Config\Scope\Converter $converter
     * @param \Ubertheme\UbThemeHelper\Model\ResourceModel\Config\Collection\ScopedFactory $collectionFactory
     * @param \Magento\Framework\App\Config $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Ubertheme\UbThemeHelper\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\App\Config\Scope\Converter $converter,
        \Ubertheme\UbThemeHelper\Model\ResourceModel\Config\Collection\ScopedFactory $collectionFactory,
        \Magento\Framework\App\Config $scopeConfig,
        \Magento\Theme\Model\Theme $theme,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_initialConfig = $initialConfig;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
        $this->theme = $theme;

        $this->logger = $logger;
        $this->_storeManager = $storeManager;

        //for back-end context
        $this->themeId = \Ubertheme\UbThemeHelper\Helper\Data::getCurrentThemeId();
        if (!$this->themeId) {
            //for front-end context
            $this->themeId = $scopeConfig->getValue(
                'design/theme/theme_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
    }

    /**
     * Read configuration data
     *
     * @param null|string $scope
     * @return array
     * @throws LocalizedException Exception is thrown when scope other than default is given
     */
    public function read($scope = null)
    {
        $scope = ($scope === null) ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : $scope;
        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Only default scope allowed"));
        }

        //get default config values (values from config.xml)
        $defaultConfig = $this->_initialConfig->getData($scope);

        //get custom config from ubthemehelper_config_data table if exists
        $customConfig = $this->getCustomConfiguration($scope);

        //replace with default configs
        $config = array_replace_recursive($defaultConfig, $customConfig);

        return $config;
    }

    private function getCustomConfiguration($scope)
    {

        $customConfig = [];

        $theme = $this->theme->load($this->themeId);
        $parentTheme = $theme->getParentTheme();
        if ($parentTheme) {
            $parentTheme2 = $parentTheme->getParentTheme();
            if ($parentTheme2) {
                $customConfig = $this->getCustomConfigByThemeId($scope, $parentTheme->getId());
            }
            $customConfig = array_replace_recursive(
                $customConfig,
                $this->getCustomConfigByThemeId($scope, $parentTheme->getId())
            );
        }

        $customConfig = array_replace_recursive(
            $customConfig,
            $this->getCustomConfigByThemeId($scope, $theme->getId())
        );

        return $customConfig;
    }

    private function getCustomConfigByThemeId($scope, $themeId)
    {
        $config = [];
        $collection = $this->_collectionFactory->create(
            ['scope' => $scope, 'theme_id' => $themeId]
        );
        $collection->addFieldToFilter('theme_id', $themeId);
        foreach ($collection as $item) {
            $config[$item->getPath()] = $item->getValue();
        }
        $config = $this->_converter->convert($config);

        return $config;
    }
}
