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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;

class DeliveryboyDetails extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * Get Deliveryboy details.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->authorizeRequest();
            $this->verifyUserData();
            $Iconheight = $IconWidth = 144 * $this->mFactor;
            $deliveryboy = $this->deliveryboy->load($this->deliveryboyId);
            if (!$deliveryboy->getId()) {
                $this->returnArray["name"] = null;
                $this->returnArray["email"] = null;
                $this->returnArray["status"] = null;
                $this->returnArray["mobile"] = null;
                $this->returnArray["address"] = null;
                $this->returnArray["latitude"] = null;
                $this->returnArray["longitude"] = null;
                $this->returnArray["vehicleType"] = null;
                $this->returnArray["onlineStatus"] = false;
                $this->returnArray["vehicleNumber"] = null;
                $this->returnArray["ownerId"] = 0;
                $this->returnArray["avatar"] = "";
                $this->returnArray["averageRating"] = null;
                $this->addExtraFieldsToDeliveryboyArray($deliveryboy);
            } else {
                $this->returnArray["name"] = $deliveryboy->getName();
                $this->returnArray["email"] = $deliveryboy->getEmail();
                $this->returnArray["status"] = $deliveryboy->getStatus();
                $this->returnArray["mobile"] = $deliveryboy->getMobileNumber();
                $this->returnArray["address"] = $deliveryboy->getAddress();
                $this->returnArray["latitude"] = $deliveryboy->getLatitude();
                $this->returnArray["longitude"] = $deliveryboy->getLongitude();
                $this->returnArray["vehicleType"] = $deliveryboy->getVehicleType();
                $this->returnArray["onlineStatus"] = (bool)$deliveryboy->getAvailabilityStatus();
                $this->returnArray["vehicleNumber"] = $deliveryboy->getVehicleNumber();
                $this->addExtraFieldsToDeliveryboyArray($deliveryboy);
                $newUrl = "";
                $basePath = $this->baseDir . DIRECTORY_SEPARATOR . $deliveryboy->getImage();
                try {
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir . DIRECTORY_SEPARATOR . "deliveryboyresized" . DIRECTORY_SEPARATOR
                            .$IconWidth . "x" . $Iconheight . DIRECTORY_SEPARATOR . $deliveryboy->getImage();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                        $newUrl = $this->deliveryboyHelper->getUrl("media") . "deliveryboyresized" .
                            DIRECTORY_SEPARATOR
                        .$IconWidth . "x" . $Iconheight . DIRECTORY_SEPARATOR . $deliveryboy->getImage();
                    }
                } catch (\Throwable $e) {
                    $this->logger->debug($e->getMessage());
                }
                // $this->returnArray["avatar"] = $newUrl;
                // $ratings = $this->ratingCollection->create()
                //     ->addFieldToSelect("deliveryboy_id")
                //     ->addFieldToSelect("rating")
                //     ->addFieldToSelect("status")
                //     ->addFieldToFilter("status", 1)
                //     ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
                // $ratings->getSelect()
                //     ->columns(
                //         [
                //             "avg" => new \Zend_Db_Expr("AVG(rating)")
                //         ]
                //     );
                // foreach ($ratings as $each) {
                //     $this->returnArray["averageRating"] = $each->getAverageRating($this->deliveryboyId);
                // }            
                $this->returnArray["avatar"] = $newUrl;
                $ratings = $this->ratingCollection->create()
                    ->addFieldToSelect("deliveryboy_id")
                    ->addFieldToSelect("rating")
                    ->addFieldToSelect("status")
                    ->addFieldToFilter("status", 1)
                    ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
                $ratings->getSelect()
                    ->columns(
                        [
                            "avg" => new \Zend_Db_Expr("AVG(rating)")
                        ]
                    );
                foreach ($ratings as $each) {
                    $this->returnArray["averageRating"] = $each->getAverageRating($this->deliveryboyId);
                }
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Vrify User data.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUserData(): void
    {
        $deliveryboyCollection = $this->deliveryboyResourceCollection
            ->create()
            ->addFieldToFilter('id', $this->deliveryboyId);
        $this->_addBeforeFiltersDeliveryboyResourceCollection($deliveryboyCollection);
        $deliveryboy = $deliveryboyCollection->getFirstItem();
    }

    /**
     * Verify Request.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? $this->storeManager->getStore()->getId();
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->deliveryboyId = (int)trim($this->wholeData["deliveryboyId"] ?? 0);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * Is Admin User.
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * Filter Delvieryboy Collection.
     *
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function _addBeforeFiltersDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {

        return $collection;
    }

    /**
     * Add Extra fields to delvieryboy array.
     *
     * @param \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface $deliveryboy
     * @return bool
     */
    protected function addExtraFieldsToDeliveryboyArray(
        \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface $deliveryboy
    ): bool {
        $store = $this->storeManager->getStore($this->storeId);
        $storeData = [
            'store_id' => $store->getId(),
            "store_code" => $store->getCode(),
            "store_websiteid" => $store->getWebsiteId(),
            "store_name" => $store->getName(),
            "store_url" => $store->getUrl(),
        ];
        $this->returnArray['vehicleList'] = $this->deliveryboyHelper->getVehicleTypeList();
        $this->returnArray['storeData'] = $storeData;
        return true;
    }

    /**
     * Authorize Request.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function authorizeRequest(): void
    {
        if (!($this->isAdmin() || $this->isDeliveryboy())) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * Is Deliveryboy.
     *
     * @return bool
     */
    protected function isDeliveryboy(): bool
    {
        return true;
    }
}
