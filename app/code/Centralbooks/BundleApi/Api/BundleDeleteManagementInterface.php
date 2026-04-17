<?php
namespace Centralbooks\BundleApi\Api;

interface BundleDeleteManagementInterface
{
    /**
     * Delete a child from bundle option.
     *
     * @param string $bundleSku
     * @param int $optionId
     * @param string $childSku
     * @return bool
     */
    public function deleteChild($bundleSku, $optionId, $childSku);
}

