<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\App;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config implements \Magento\Framework\App\Config\ScopeConfigInterface
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * @var \Ubertheme\UbThemeHelper\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @param \Ubertheme\UbThemeHelper\App\Config\ScopePool $scopePool
     */
    public function __construct(\Ubertheme\UbThemeHelper\App\Config\ScopePool $scopePool)
    {
        $this->_scopePool = $scopePool;
    }

    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    )
    {

        return $this->_scopePool->getScope($scope, $scopeCode)->getValue($path);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return (bool)$this->getValue($path, $scope, $scopeCode);
    }

}
