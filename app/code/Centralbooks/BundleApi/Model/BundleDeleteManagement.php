<?php
namespace Centralbooks\BundleApi\Model;

use Centralbooks\BundleApi\Api\BundleDeleteManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class BundleDeleteManagement implements BundleDeleteManagementInterface
{
    private $productRepository;
    private $resource;
    private $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function deleteChild($bundleSku, $optionId, $childSku)
    {
        try {
            // Load parent & child IDs
            $parentId = $this->productRepository->get($bundleSku)->getId();
            $childId  = $this->productRepository->get($childSku)->getId();
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__("Invalid SKU(s): %1 / %2", $bundleSku, $childSku));
        }

        $connection = $this->resource->getConnection();
        $table = $connection->getTableName('catalog_product_bundle_selection');

        try {
            // Hard delete selection row
            $connection->delete($table, [
                'parent_product_id = ?' => $parentId,
                'option_id = ?'         => (int)$optionId,
                'product_id = ?'        => $childId
            ]);

            $this->logger->info("Bundle child deleted", [
                'bundleSku' => $bundleSku,
                'optionId'  => $optionId,
                'childSku'  => $childSku
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Bundle child delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

