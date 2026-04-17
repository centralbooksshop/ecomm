<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Helper;

use Psr\Log\LoggerInterface;

class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;
    
    /**
     * @var \Magento\Store\Block\Switcher
     */
    private $storeSwitcher;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    private $imageFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Store\Block\Switcher $storeSwitcher
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Webkul\DeliveryBoy\Helper\Data $helperData
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Block\Switcher $storeSwitcher,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        
        $this->imageHelper = $imageHelper;
        $this->imageFactory = $imageFactory;
        $this->storeSwitcher = $storeSwitcher;
        $this->helperData = $helperData;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Url for Magento dir.
     *
     * @param string $dir
     * @return string
     */
    public function getUrl(string $dir)
    {
        return $this->storeManager->getStore()->getBaseUrl($dir);
    }

    /**
     * Get Store Data.
     *
     * @param int $websiteId
     * @return array
     */
    public function getStoreData(string $websiteId = "1"): array
    {
        $storeData = [];
        try {
            foreach ($this->storeSwitcher->getGroups() as $group) {
                if ($group->getWebsiteId() == $websiteId) {
                    $groupArr = [];
                    $groupArr["id"] = $group->getGroupId();
                    $groupArr["name"] = $group->getName();
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        if (!$store->isActive()) {
                            continue;
                        }
                        $storeArr = [];
                        $storeArr["id"] = $store->getStoreId();
                        $code = explode("_", $this->helperData->getLocaleCodes($store->getId()));
                        $storeArr["code"] = $code[0];
                        $storeArr["name"] = $store->getName();
                        $groupArr["stores"][] = $storeArr;
                    }
                    $storeData[] = $groupArr;
                } else {
                    continue;
                }
            }
            return $storeData;
        } catch (\Throwable $t) {
            $this->logger->critical($t->getMessage());
        }
    }

    /**
     * String html tags.
     *
     * @param string $data data
     * @return string
     */
    public function stripTags($data)
    {
        return strip_tags($data);
    }

    /**
     * Get Product Image Url.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $size
     * @param string $imageType
     * @param bool $keepFrame
     * @return string
     */
    public function getImageUrl(
        \Magento\Catalog\Model\Product $product,
        int $size,
        string $imageType = "product_page_image_small",
        bool $keepFrame = true
    ): string {
        try {
            return $this->imageHelper
                ->init($product, $imageType)
                ->keepFrame($keepFrame)
                ->resize($size)
                ->getUrl();
        } catch (\Throwable $t) {
            $this->logger->critical($t->getMessage());
        }
    }

    /**
     * Resize Image.
     *
     * @param string $basePath
     * @param string $newPath
     * @param integer $imageWidth
     * @param integer $imageHeight
     * @param bool $forCustomer
     *
     * @return string
     */
    public function resizeNCache(
        string $basePath,
        string $newPath,
        int $imageWidth,
        int $imageHeight,
        bool $forCustomer = false
    ) {
        try {
            if (!$this->fileDriver->isFile($newPath) || $forCustomer) {
                $imageObj = $this->imageFactory->create($basePath);
                $imageObj->keepAspectRatio(false);
                $imageObj->backgroundColor([255, 255, 255]);
                $imageObj->keepFrame(false);
                $imageObj->resize($imageWidth, $imageHeight);
                $imageObj->save($newPath);
            }
        } catch (\Throwable $t) {
            $this->logger->critical($t->getMessage());
        }
    }

    /**
     * Resize Image Helper Method.
     *
     * @param float $mFactor
     * @param string $relativePath
     * @return void
     */
    public function resizeAndGetDeliveryboyImageFromMFactorAndImageRelativePath(
        $mFactor,
        $relativePath
    ) {
        try {
            $imagePath = $this->getDeliveryboyImageUrlFromImageRelativePath($relativePath);
            if ($this->fileDriver->isFile($imagePath)) {
                $resizedImagePath = $this->getDeliveryboyResizedImagePathFromMFactorRelativeImagePath(
                    $mFactor,
                    $relativePath
                );
                $this->resizeNCache(
                    $imagePath,
                    $resizedImagePath,
                    $this->getIconWidthFromMobileFactor($mFactor),
                    $this->getIconHeightFromMobileFactor($mFactor)
                );
                $resizedImageUrl = $this->getDeliveryboyResizedImageUrlFromMFactorRelativeImagePath(
                    $mFactor,
                    $relativePath
                );
                return $resizedImageUrl;
            }
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
        return "";
    }

    /**
     * Get Icon WIdth
     *
     * @param float $mFactor
     * @return float
     */
    public function getIconWidthFromMobileFactor($mFactor)
    {
        return 144 * $mFactor;
    }

    /**
     * Get Icon Height
     *
     * @param float $mFactor
     * @return float
     */
    public function getIconHeightFromMobileFactor($mFactor)
    {
        return 144 * $mFactor;
    }

    /**
     * Get Media Directory.
     *
     * @return string
     */
    public function getMediaDirectory()
    {
        return $this->directoryList->getPath("media");
    }

    /**
     * Get Media Directory from relativePath.
     *
     * @param string $relativePath
     * @return string
     */
    public function getDeliveryboyImageUrlFromImageRelativePath($relativePath)
    {
        return $this->getMediaDirectory() . DIRECTORY_SEPARATOR . $relativePath;
    }

    /**
     * Get Media Directory using mFactor and Relative Path.
     *
     * @param float $mFactor
     * @param string $relativePath
     * @return string
     */
    public function getDeliveryboyResizedImageUrlFromMFactorRelativeImagePath(
        $mFactor,
        $relativePath
    ) {
        $imageHeight = $this->getIconHeightFromMobileFactor($mFactor);
        $imageWidth = $this->getIconWidthFromMobileFactor($mFactor);
        return $this->getUrl("media") . "deliveryboyresized" .
            DIRECTORY_SEPARATOR .
            $imageWidth . "x" . $imageHeight . DIRECTORY_SEPARATOR .
            $relativePath;
    }

    /**
     * Get Resized Image Path using mFactor and Relative Path.
     *
     * @param float $mFactor
     * @param string $relativePath
     * @return string
     */
    public function getDeliveryboyResizedImagePathFromMFactorRelativeImagePath(
        $mFactor,
        $relativePath
    ) {
        $imageHeight = $this->getIconHeightFromMobileFactor($mFactor);
        $imageWidth = $this->getIconWidthFromMobileFactor($mFactor);
        return $this->getMediaDirectory() . "deliveryboyresized" .
            DIRECTORY_SEPARATOR .
            $imageWidth . "x" . $imageHeight . DIRECTORY_SEPARATOR .
            $relativePath;
    }
}
