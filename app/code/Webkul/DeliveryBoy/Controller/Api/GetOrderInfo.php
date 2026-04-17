<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software public Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\DeliveryBoy\Controller\Api;

use Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

class GetOrderInfo extends AbstractDeliveryboy
{
    public const ALLOWED_SHIPPING = "deliveryboy/configuration/allowed_shipping";
    public const DS = "/";

    /**
     * @var array
     */
    protected $orderProductsHash = [];
    
    /**
     * Get ORder Info.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
            $returnArray['success'] = false;
            if (empty($order->getId())) {
                $returnArray["message"] = __("Invalid Order.");
                return $this->getJsonResponse($returnArray);
            }
            $customerId = $order->getCustomerId();
            $shippingMethod = $order->getShippingMethod();
            if ($this->deliveryboyHelper->isAllowedForShipping($shippingMethod)) {
                $orderVisibleItems = $order->getAllVisibleItems();
                $shippingMethod = $order->getShippingMethod();
                $rating = $this->ratingFactory->create();
                $connection = $this->resource->getConnection();
                $tableName  = $this->resource->getTableName("deliveryboy_deliveryboy");
                $collection = $this->deliveryboyOrderResourceCollection->create()->addFieldToFilter(
                    "increment_id",
                    $order->getIncrementId()
                );
                 $collection->getSelect()
                    ->reset(\Magento\Framework\DB\Select::COLUMNS);
                $deliveryboyParams = [
                    "name" =>            "deliveryboy.name",
                    "email" =>           "deliveryboy.email",
                    "avatar" =>          "deliveryboy.image",
                    "vehicleType"=>      "deliveryboy.vehicle_type",
                    "mobileNumber"=>     "deliveryboy.mobile_number",
                    "vehicleNumber"=>    "deliveryboy.vehicle_number",
                    "id" =>              "deliveryboy.id",
                    "deliveryBoyLat" =>  "deliveryboy.latitude",
                    "deliveryBoyLong" => "deliveryboy.longitude",
                    "picked" =>          "main_table.picked",
                    "otp" =>             "main_table.otp",
                ];
                $this->modifyDelBoyParams($deliveryboyParams);

                $collection->getSelect()
                    ->join(
                        [
                            "deliveryboy" => $tableName
                        ],
                        "main_table.deliveryboy_id=deliveryboy.id",
                        $deliveryboyParams
                    );
                $this->addFiltersDelBoyOrderColl($collection);
                foreach ($collection as $item) {
                    $deliveryboyDetails = $item->getData();
                    $orderItems = $order->getItems();
                    $deliveryboyDetails['products'] = $this->getDeliveryboyOrderProducts($item, $orderVisibleItems);
                    unset($deliveryboyDetails['productIds']);
                    $deliveryboyDetails['isEligibleForDeliveryBoy'] = true;
                    $Iconheight = $IconWidth = 144;
                    $newUrl = "";
                    $basePath = $this->baseDir . DIRECTORY_SEPARATOR . $deliveryboyDetails['avatar'];
                    try {
                        if ($this->fileDriver->isFile($basePath)) {
                            $newPath = $this->baseDir . DIRECTORY_SEPARATOR;
                            $newPath .= "deliveryboyresized" . DIRECTORY_SEPARATOR . $IconWidth . "x";
                            $newPath .= $Iconheight . DIRECTORY_SEPARATOR . $deliveryboyDetails['avatar'];
                            $this->helperCatalog->resizeNCache(
                                $basePath,
                                $newPath,
                                $IconWidth,
                                $Iconheight
                            );
                            $newUrl = $this->helper->getUrl("media");
                            $newUrl .= "deliveryboyresized" . DIRECTORY_SEPARATOR . $IconWidth . "x";
                            $newUrl .= $Iconheight . DIRECTORY_SEPARATOR . $deliveryboyDetails['avatar'];
                        }
                    } catch (\Throwable $t) {
                        $this->logger->debug($t->getMessage());
                    }
                    $deliveryboyDetails['avatar'] = $newUrl;
                    $deliveryboyDetails['customerId'] = $customerId;
                    $deliveryboyDetails['picked'] = (bool)$deliveryboyDetails['picked'];
                    $deliveryboyDetails['rating'] =
                        $rating->getAverageRating($deliveryboyDetails['id']);
                    $returnArray['deliveryBoys'][] = $deliveryboyDetails;
                    $returnArray['adminAddress'] = $this->deliveryboyHelper->getConfigData(
                        "deliveryboy/configuration/warehouse_address"
                    );
                }
                $returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
            }
        } catch (\Throwable $e) {
            $returnArray["message"] = __($e->getMessage());
            $returnArray["trace"] = __($e->getTraceAsString());
        }

        return $this->getJsonResponse($returnArray);
    }

    /**
     * Get Deliveyrboy Order Products
     *
     * @param DeliveryboyOrder $deliveryboyOrder
     * @param OrderItem[] $orderVisibleItems
     * @return string[]
     */
    public function getDeliveryboyOrderProducts($deliveryboyOrder, $orderVisibleItems)
    {
        $incrementId = $deliveryboyOrder->getIncrementId();
        if (empty($this->orderProductsHash[$incrementId])) {
            $adminItems = $this->getOrderItemsNameArray($orderVisibleItems);
            $this->orderProductsHash[$incrementId][$this->deliveryboyHelper->getAdminId()]
                = $adminItems;
        }
        return $this->orderProductsHash[$incrementId];
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
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request."));
        }
    }

    /**
     * Get ORder Items Name Array.
     *
     * @param \Magento\Sales\Model\Order\Item[] $orderItems
     * @return \Magento\Sales\Model\Order\Item[]
     */
    public function getOrderItemsNameArray($orderItems)
    {
        $orderItemsNameArray = [];

        foreach ($orderItems as $orderItem) {
            $orderItemsNameArray[] = $this->getOrderItemReadableName($orderItem);
        }

        return $orderItemsNameArray;
    }

    /**
     * Get Order Items Readable name.
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return string
     */
    public function getOrderItemReadableName($orderItem)
    {
        return $this->getOrderItemName($orderItem) .
            ' (x' . (int)$orderItem->getQtyOrdered() . ')';
    }

    /**
     * Get Order Item Name.
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return string
     */
    public function getOrderItemName($orderItem)
    {
        $name = $orderItem->getName();
        $options = $orderItem->getProductOptions();
        $name = empty($options['simple_name']) ? $name : $options['simple_name'];

        return $name;
    }

    /**
     * Modify param hook.
     *
     * @param array $deliveryboyParams
     * @return $this
     */
    public function modifyDelBoyParams(&$deliveryboyParams)
    {
        return $this;
    }

    /**
     * Filter Deliveryboy Order Collection.
     *
     * @param DeliveryboyOrder $collection
     * @return $this
     */
    public function addFiltersDelBoyOrderColl($collection)
    {
        return $this;
    }
}
