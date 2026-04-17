<?php
/**
 * Initial configuration data container. Provides interface for reading initial config values
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ubertheme\UbThemeHelper\Framework\App\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Initial
{
    /**
     * Cache identifier used to store initial config
     */
    const CACHE_ID = 'ubthemehelper_initial_config';

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Config metadata
     *
     * @var array
     */
    protected $_metadata = [];

    protected $themeId;

    /**
     * @param Initial\Reader $reader
     * @param \Magento\Framework\App\Config $scopeConfig
     * @param \Magento\Framework\App\Cache\Type\Config $cache
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Ubertheme\UbThemeHelper\Framework\App\Config\Initial\Reader $reader,
        \Magento\Framework\App\Config $scopeConfig,
        \Magento\Framework\App\Cache\Type\Config $cache
    )
    {
        //for back-end context
        $this->themeId = \Ubertheme\UbThemeHelper\Helper\Data::getCurrentThemeId();
        if (!$this->themeId) {
            //for front-end context
            $this->themeId = $scopeConfig->getValue(
                'design/theme/theme_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        //read default config
        $cacheIdentifier = self::CACHE_ID . '_theme_' . $this->themeId;
        $data = $cache->load($cacheIdentifier);
        if (!$data) {
            $data = $reader->read();
            $cache->save(serialize($data), $cacheIdentifier);
        } else {
            $data = unserialize($data);
        }

        if (isset($data['data'])) {
            $this->_data = $data['data'];
        }
        if (isset($data['metadata'])) {
            $this->_metadata = $data['metadata'];
        }
    }

    /**
     * Get initial data by given scope
     *
     * @param string $scope Format is scope type and scope code separated by pipe: e.g. "type|code"
     * @return array
     */
    public function getData($scope)
    {
        list($scopeType, $scopeCode) = array_pad(explode('|', $scope), 2, null);

        if (ScopeConfigInterface::SCOPE_TYPE_DEFAULT == $scopeType) {
            return isset($this->_data[$scopeType]) ? $this->_data[$scopeType] : [];
        } elseif ($scopeCode) {
            return isset($this->_data[$scopeType][$scopeCode]) ? $this->_data[$scopeType][$scopeCode] : [];
        }
        return [];
    }

    /**
     * Get configuration metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }
}
