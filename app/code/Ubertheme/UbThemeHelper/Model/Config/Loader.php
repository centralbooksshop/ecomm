<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

/**
 * Theme configuration loader
 */

namespace Ubertheme\UbThemeHelper\Model\Config;

class Loader
{
    /**
     * Config data factory
     *
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ubertheme\UbThemeHelper\App\Config\ScopePool $scopePool
    )
    {
        $this->_objectManager = $objectManager;
        $this->_configValueFactory = $configValueFactory;
        $this->_scopePool = $scopePool;
    }

    /**
     * Get configuration value by path
     *
     * @param string $path
     * @param string $scope
     * @param string $scopeId
     * @param bool $full
     * @return array
     */
    public function getPath($data, $path = null)
    {
        if (!is_array($data)) {
            $this->data[$path] = $data;
        } else {
            foreach ($data as $key => $value) {
                if ($path) {
                    $this->getPath($value, $path . '/' . $key);
                } else {
                    $this->getPath($value, $key);
                }
            }
        }
    }

    public function getConfigByPathForLoadForm($path, $scope, $scopeId, $themeId, $full = true)
    {
        $configDataCollection = $this->_objectManager->create('Ubertheme\UbThemeHelper\Model\ResourceModel\Config\Data\Collection');
        $configDataCollection->addScopeFilter($scope, $scopeId, $themeId, $path);

        $config = [];
        $configDataCollection->load();
        foreach ($configDataCollection->getItems() as $data) {
            if ($full) {
                $config[$data->getPath()] = [
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'config_id' => $data->getConfigId(),
                ];
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }

        if ($full) {
            $data = $this->_scopePool->getScope($scope, $scopeId)->getValue();
            $this->getPath($data);
            $config2 = $this->data;
            foreach ($config2 as $path => $value) {
                $config2[$path] = [
                    'path' => $path,
                    'value' => $value,
                    'config_id' => '',
                ];
            }
        } else {
            $data = $this->_scopePool->getScope($scope, $scopeId)->getValue();
            $this->getPath($data);
            $config2 = $this->data;
        }

        $config = array_merge($config2, $config);

        return $config;
    }

    public function getConfigByPath($path, $scope, $scopeId, $themeId, $full = true)
    {
        $configDataCollection = $this->_objectManager->create('Ubertheme\UbThemeHelper\Model\ResourceModel\Config\Data\Collection');
        $configDataCollection->addScopeFilter($scope, $scopeId, $themeId, $path);

        $config = [];
        $configDataCollection->load();
        foreach ($configDataCollection->getItems() as $data) {
            if ($full) {
                $config[$data->getPath()] = [
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'config_id' => $data->getConfigId(),
                ];
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }

        return $config;
    }
}
