<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\App\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ScopePool
{
    const CACHE_TAG = 'config_scopes';

    /**
     * @var \Ubertheme\UbThemeHelper\App\Config\Scope\ReaderPoolInterface
     */
    protected $_readerPool;

    /**
     * @var \Magento\Framework\App\Config\DataFactory
     */
    protected $_dataFactory;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var string
     */
    protected $_cacheId;

    /**
     * @var DataInterface[]
     */
    protected $_scopes = [];

    /**
     * @var \Magento\Framework\App\ScopeResolverPool
     */
    protected $_scopeResolverPool;

    protected $themeId;

    /**
     * @param Scope\ReaderPoolInterface $readerPool
     * @param \Magento\Framework\App\Config\DataFactory $dataFactory
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\App\ScopeResolverPool $scopeResolverPool
     * @param \Magento\Framework\App\Config $scopeConfig
     * @param string $cacheId
     */
    public function __construct(
        \Ubertheme\UbThemeHelper\App\Config\Scope\ReaderPoolInterface $readerPool,
        \Magento\Framework\App\Config\DataFactory $dataFactory,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\App\ScopeResolverPool $scopeResolverPool,
        \Magento\Framework\App\Config $scopeConfig,
        $cacheId = 'ubthemehelper_config_cache'
    )
    {
        $this->_readerPool = $readerPool;
        $this->_dataFactory = $dataFactory;
        $this->_cache = $cache;
        $this->_scopeResolverPool = $scopeResolverPool;
        $this->_cacheId = $cacheId;

        //for back-end context
        $this->themeId = \Ubertheme\UbThemeHelper\Helper\Data::getCurrentThemeId();
        if (!$this->themeId) {
            //for front-end context
            $this->themeId = $scopeConfig->getValue('design/theme/theme_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * Retrieve config section
     *
     * @param string $scopeType
     * @param string|\Magento\Framework\DataObject|null $scopeCode
     * @return \Magento\Framework\App\Config\DataInterface
     */
    public function getScope($scopeType, $scopeCode = null)
    {
        $scopeCode = $this->_getScopeCode($scopeType, $scopeCode);
        $code = $scopeType . '|' . $scopeCode . '|' . $this->themeId;
        if (!isset($this->_scopes[$code])) {
            $cacheKey = $this->_cacheId . '|' . $code;
            $data = $this->_cache->load($cacheKey);
            if ($data) {
                $data = unserialize($data);
            } else {
                $reader = $this->_readerPool->getReader($scopeType);
                if ($scopeType === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                    $data = $reader->read();
                } else {
                    $data = $reader->read($scopeCode);
                }
                $this->_cache->save(serialize($data), $cacheKey, [self::CACHE_TAG]);
            }

            $this->_scopes[$code] = $this->_dataFactory->create(['data' => $data]);
        }

        return $this->_scopes[$code];
    }

    /**
     * Clear cache of all scopes
     *
     * @return void
     */
    public function clean()
    {
        $this->_scopes = [];
        $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
    }

    /**

     * Retrieve scope code value
     *
     * @param string $scopeType
     * @param string|\Magento\Framework\DataObject|null $scopeCode
     * @return string
     */
    protected function _getScopeCode($scopeType, $scopeCode)
    {
        if (($scopeCode === null || is_numeric($scopeCode))
            && $scopeType !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ) {
            $scopeResolver = $this->_scopeResolverPool->get($scopeType);
            $scopeCode = $scopeResolver->getScope($scopeCode);
        }

        if ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
            $scopeCode = $scopeCode->getCode();
        }

        return $scopeCode;
    }

}
