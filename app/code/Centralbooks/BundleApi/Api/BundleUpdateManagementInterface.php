<?php

namespace Centralbooks\BundleApi\Api;

interface BundleUpdateManagementInterface
{
    /**
     * @param string $bundleSku
     * @param int $optionId
     * @param string $newTitle
     * @return bool
     */
    public function updateGroupName($bundleSku, $optionId, $newTitle);
}

