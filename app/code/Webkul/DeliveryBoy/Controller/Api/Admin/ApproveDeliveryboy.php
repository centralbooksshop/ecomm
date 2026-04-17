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
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Model\Deliveryboy\Source\ApproveStatus;

class ApproveDeliveryboy extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * Approve Deliveryboy.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $deliveryboyCollection = $this->deliveryboyResourceCollection->create();
            $deliveryboy = $deliveryboyCollection->addFieldToFilter('id', $this->deliveryboyId)
                ->getFirstItem();
            if ($deliveryboy->getId() == 0) {
                throw new LocalizedException(__("Invalid delivery boy."));
            }
            $currentApproveStatus = $deliveryboy->getApproveStatus();
            if ($currentApproveStatus != ApproveStatus::STATUS_APPROVED &&
                $this->approveStatus == ApproveStatus::STATUS_APPROVED
            ) {
                $this->deliveryboyHelper->approveDeliveryboy($deliveryboy->getId());
            } elseif ($this->approveStatus == ApproveStatus::STATUS_NOT_APPROVED &&
                $currentApproveStatus == ApproveStatus::STATUS_APPROVED
            ) {
                $deliveryboy->setApproveStatus(ApproveStatus::STATUS_NOT_APPROVED)
                    ->save();
                $deliveryboy->delete();
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify Request Data.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->mFactor = trim($this->wholeData["mFactor"] ?? 1);
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->approveStatus = trim(
                $this->wholeData["isApproved"]
                ?? ApproveStatus::STATUS_APPROVED
            );
            $this->deliveryboyId = trim($this->wholeData["deliveryBoyId"]);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * Get Image Url.
     *
     * @param string $imagePath
     * @return string
     */
    public function getDeliveryBoyImageUrl($imagePath)
    {
        $Iconheight = $IconWidth = 144 * $this->mFactor;
        $newUrl = "";
        $basePath = $this->baseDir . DIRECTORY_SEPARATOR . $imagePath;
        try {
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir . DIRECTORY_SEPARATOR . "deliveryboyresized" . DIRECTORY_SEPARATOR .
                    $IconWidth . "x" . $Iconheight . DIRECTORY_SEPARATOR . $imagePath;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                $newUrl = $this->deliveryboyHelper->getUrl("media") . "deliveryboyresized"
                    . DIRECTORY_SEPARATOR .
                    $IconWidth . "x" . $Iconheight . DIRECTORY_SEPARATOR . $imagePath;
            }
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
        return $newUrl;
    }

    /**
     * Filter Deliveryboy collection.
     *
     * @param DeliveryboyCollection $deliveryboyCollection
     * @return DeliveryboyCollection
     */
    public function addFilter($deliveryboyCollection)
    {
        $deliveryboyCollection->addFieldToFilter(
            'approve_status',
            ApproveStatus::STATUS_NOT_APPROVED
        );
        return $deliveryboyCollection;
    }
}
