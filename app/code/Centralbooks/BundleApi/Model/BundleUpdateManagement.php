<?php

namespace Centralbooks\BundleApi\Model;

use Centralbooks\BundleApi\Api\BundleUpdateManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class BundleUpdateManagement implements BundleUpdateManagementInterface
{
    protected $productRepository;
    protected $resource;
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function updateGroupName($bundleSku, $optionId, $newTitle)
    {
        try {
            $product = $this->productRepository->get($bundleSku);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__("Bundle SKU not found: %1", $bundleSku));
        }

        $connection = $this->resource->getConnection();
        $optionTable = $connection->getTableName('catalog_product_bundle_option_value');

        $connection->update(
            $optionTable,
            ['title' => $newTitle],
            ['option_id = ?' => (int)$optionId, 'store_id = ?' => 0]
        );

        $this->logger->info("Group name updated", [
            'bundleSku' => $bundleSku,
            'optionId' => $optionId,
            'newTitle' => $newTitle
        ]);

        return true;
    }
}

