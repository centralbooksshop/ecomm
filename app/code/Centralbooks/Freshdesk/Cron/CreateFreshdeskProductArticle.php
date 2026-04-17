<?php
namespace Centralbooks\Freshdesk\Cron;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Centralbooks\Freshdesk\Helper\Api;
use Magento\Framework\App\ResourceConnection;
use Centralbooks\Freshdesk\Helper\Config as ConfigHelper;

class CreateFreshdeskProductArticle
{
    protected $productCollectionFactory;
    protected $api;
    protected $logger;
	protected $configHelper;

    const START_DATE = '2025-12-14 00:00:00';

    public function __construct(
        CollectionFactory $productCollectionFactory,
		ConfigHelper $configHelper,
        Api $api
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
		$this->configHelper = $configHelper;
        $this->api = $api;

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/freshdesk_product_cron.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);
    }

    public function execute()
    {
        
		if (!$this->configHelper->isProductCronEnabled()) {
			$this->logger->info('Freshdesk Product Cron is DISABLED via admin config');
			return;
		}
		
        $this->logger->info("Freshdesk Product Cron is started ".date("Y-m-d H:i:s"));
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;

        try {
            /* ---------------- PRODUCTS ---------------- */
            $startDate = date('Y-m-d H:i:s', strtotime('-1 days'));
            $products = $this->productCollectionFactory->create()
                ->addAttributeToSelect(['name','sku','updated_at','school_name','status'])
                ->addAttributeToFilter('status', 1)
                ->addFieldToFilter('type_id', 'bundle')
                ->addAttributeToFilter('updated_at', ['gteq' => $startDate]);
            $this->logger->info("Freshdesk processing product count is ".count($products));
            /* ---------------- DB ---------------- */
            $resource   = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ResourceConnection::class);
            $connection = $resource->getConnection();
            $schoolTable = $resource->getTableName('schools_registered');

            /* ---------------- CATEGORY ---------------- */
            $categoryName = 'CBS HUB School List 2026-27';
            $categoryId = $this->api->getCategoryIdByName($categoryName)
                ?: $this->api->createCategory($categoryName);

            if (!$categoryId) {
                $this->logger->err("Category not found or created");
                return;
            }

            /* ---------------- FETCH FOLDERS ONCE ---------------- */
            $folders = [];
            try {
                $folders = $this->api->getFoldersByCategoryId($categoryId);
                $this->logger->info("Freshdesk processing folders count is ".count($folders));
                $this->logger->info(
                    "Fetched " . (is_array($folders) ? count($folders) : 0) . " folders from Freshdesk"
                );
            } catch (\Exception $e) {
                $this->logger->err("Folder fetch failed: " . $e->getMessage());
            }

            /* ================= PRODUCT LOOP ================= */
            foreach ($products as $product) {

                $this->logger->info("Product Type for {$product->getSku()} = {$product->getTypeId()}");
                $this->logger->info("Product Name {$product->getName()}");

                /* ---------- SCHOOL NAME ---------- */
                $schoolName = $connection->fetchOne(
                    "SELECT school_name_text FROM {$schoolTable} WHERE school_name = ?",
                    [$product->getSchoolName()]
                );

                $schoolName = trim(preg_replace('/\s+/u', ' ', (string)$schoolName));

                if ($schoolName === '') {
                    $this->logger->info("Skipping product due to empty school name");
                    continue;
                }

                $normalizedSchool = $this->normalizeSchoolName($schoolName);

                $this->logger->info(
                    "SANITIZED schoolName => [{$schoolName}] | normalized => [{$normalizedSchool}]"
                );

                /* ---------- FIND FOLDER ---------- */
                $folderId = null;

                foreach ($folders as $folder) {
                    if (!isset($folder['id'], $folder['name'])) {
                        continue;
                    }

                    $this->logger->info(
                        "COMPARE => folder: " . $this->normalizeSchoolName($folder['name']) .
                        " | school: " . $normalizedSchool
                    );

                    if ($this->normalizeSchoolName($folder['name']) === $normalizedSchool) {
                        $folderId = $folder['id'];
                        $this->logger->info(
                            "Matched folder '{$folder['name']}' for school '{$schoolName}'"
                        );
                        break;
                    }
                }

                /* ---------- CREATE FOLDER IF NEEDED ---------- */
                if (!$folderId) {
                    $this->logger->info("Folder NOT found — creating for school: {$schoolName}");
                    $folderId = $this->api->createFolder($categoryId, $schoolName);

                    if ($folderId) {
                        // update local cache
                        $folders[] = [
                            'id'   => $folderId,
                            'name' => $schoolName
                        ];
                    } else {
                        $this->logger->err("Failed to create folder for {$schoolName}");
                        continue;
                    }
                }

                /* ---------- BUNDLE HTML ---------- */
                $bundleHtml = '<p>No bundle items</p>';

                if ($product->getTypeId() === 'bundle') {
                    $bundleHtml = '<h3>Bundle Items</h3>
                    <table border="1" style="border-collapse:collapse;width:100%;font-family:Arial;">
                    <tr style="background:#555;color:white;">
                        <th>Group Name</th>
                        <th>Item Name</th>
                        <th>SKU</th>
                        <th>Given Options</th>
                        <th>Default Qty</th>
                        <th>Price</th>
                    </tr>';

                    $typeInstance = $product->getTypeInstance();
                    $typeInstance->setStoreFilter($product->getStoreId(), $product);

                    $options = $typeInstance->getOptionsCollection($product);
                    $selections = $typeInstance->getSelectionsCollection(
                        $typeInstance->getOptionsIds($product),
                        $product
                    );

                    foreach ($options as $option) {
                        foreach ($selections as $selection) {
                            if ($selection->getOptionId() == $option->getOptionId()) {
                                $bundleHtml .= '<tr>
                                    <td>' . $option->getTitle() . '</td>
                                    <td>' . $selection->getName() . '</td>
                                    <td>' . $selection->getSku() . '</td>
                                    <td>' . $this->getGivenOptionLabel($selection) . '</td>
                                    <td>' . (float)$selection->getSelectionQty() . '</td>
                                    <td>' . number_format($selection->getPrice(), 2) . '</td>
                                </tr>';
                            }
                        }
                    }
                    $bundleHtml .= '</table><br>';
                }

                /* ---------- ARTICLE PAYLOAD ---------- */
                $title = 'Product: ' . $product->getName();

                $payload = [
                    'title'       => $title,
                    'status'      => 2,
                    'description' =>
                        '<p><b>Product:</b> ' . $product->getName() . '</p>' .
                        '<p><b>SKU:</b> ' . $product->getSku() . '</p>' .
                        '<p><b>School:</b> ' . $schoolName . '</p>' .
                        '<p><b>Updated At:</b> ' . $product->getUpdatedAt() . '</p>' .
                        $bundleHtml
                ];

                /* ---------- CREATE / UPDATE ARTICLE ---------- */
                $existingArticleId = null;
                $articles = $this->api->getArticlesByFolderId($folderId) ?: [];

                foreach ($articles as $article) {
                    if (isset($article['title'], $article['id']) &&
                        strcasecmp(trim($article['title']), trim($title)) === 0
                    ) {
                        $existingArticleId = $article['id'];
                        break;
                    }
                }

                if ($existingArticleId) {
                    $this->logger->info("Updating article for SKU: {$product->getSku()}");
                    $this->api->updateArticle($existingArticleId, $payload);
                } else {
                    $this->logger->info("Creating article for SKU: {$product->getSku()}");
                    $this->api->createArticle($folderId, $payload);
                }
            }

        } catch (\Exception $e) {
            $this->logger->err($e->getMessage());
        }

        $endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->logger->info('Freshdesk CRON END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
            ' processed '. $processed);
    }

    /* ================= HELPERS ================= */

    private function getGivenOptionLabel($selection)
    {
        $given = (int)$selection->getData('custom_field');
        return $given === 1 ? 'Will be given' :
               ($given === 2 ? 'This item will be issued by school' : '');
    }

    private function normalizeSchoolName($name)
    {
        $name = mb_strtolower($name, 'UTF-8');
        $name = str_replace(["’", "`", "´"], "'", $name);
        $name = preg_replace('/\bst\.?\b/', 'saint', $name);
        $name = str_replace(['highschool', 'high school'], 'highschool', $name);
        $name = str_replace("'", '', $name);
        return preg_replace('/[^a-z0-9]/', '', $name);
    }
}
