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

class Dashboard extends AbstractDeliveryboy
{
    public const ENABLED = 1;
    public const MAGE_STATUS_PENDING = 'pending';

    /**
     * Get Dashboard Data.
     *
     * @return \Magento\Framework\Conroller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->validateRequestData();
            if ($this->storeId == 0) {
                $this->storeId = $this->websiteManager->create()
                    ->load($this->websiteId)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();
                $this->returnArray["storeId"] = $this->storeId;
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $returnData = $this->verifyUsernData();
            if ($returnData["type"] === "collection") {
                $deliveryboyCollection = $returnData["data"];
                $deliveryboyList = [];
                foreach ($deliveryboyCollection as $each) {
                    $deliveryboyList[] = $this->extractDeliveryboyNecessaryFileldsFromDeliveryboyObject(
                        $each
                    );
                }
                $this->returnArray["deliveryboyList"] = $deliveryboyList;
            } else {
                $deliveryboy = $returnData["data"];
                $this->returnArray["deliveryboyList"][] =
                    $this->extractDeliveryboyNecessaryFileldsFromDeliveryboyObject(
                        $deliveryboy
                    );
            }
            // daily order list /////////////////////////////////////////////////////
            $this->getDailyOrderList();
            // weekly order list ////////////////////////////////////////////////////
            $this->getWeeklyOrderList();
            // monthly order list ///////////////////////////////////////////////////
            $this->getMonthlyOrderList();
            // yearly order list ////////////////////////////////////////////////////
            $this->getYearlyOrderList();

            $this->returnArray['orderStatus'] = $this->getOrderStatus();
            $this->returnArray['selectedCurrencySymbol'] = $this->storeManager->getStore()
                ->getCurrentCurrency()
                ->getCurrencySymbol();

            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["storeData"] = $this->helperCatalog->getStoreData(
                $this->websiteId
            );
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify user data.
     *
     * @return array
     * @throws LocalizedException
     */
    protected function verifyUsernData(): array
    {
        $returnData = [
            "type" => "collection",
            "data" => ""
        ];
        if ($this->isDeliveryboy()) {
            $deliveryboy = $this->deliveryboy->load($this->userId);
            if ($deliveryboy->getId() != $this->userId) {
                throw new LocalizedException(__("Unauthorized access."));
            }
            $returnData["type"] = "object";
            $returnData["data"] = $deliveryboy;
        } else {
            $deliveryboyCollection = $this->deliveryboyResourceCollection
                ->create()
                ->addFieldToFilter("status", self::ENABLED);
            $this->applyFiltersDeliveryboyResourceCollection($deliveryboyCollection);
            $returnData["data"] = $deliveryboyCollection;
        }

        return $returnData;
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
            $this->userId = trim($this->wholeData["userId"] ?? 0);
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->websiteId = trim($this->wholeData["websiteId"] ?? 1);
            $this->isDeliveryboy = trim($this->wholeData["isDeliveryboy"] ?? false);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    public function deliveryOrdersDetails($deliveryBoyId, $driverErpId)
    {
	if(!empty($driverErpId)){
	    $connection = $this->resource->getConnection();
 	    $tableName = $this->resource->getTableName("centralbooks_transactions"); 
	    $query = $connection->select()->from(
                ['main_table' => $tableName],
                ['driver_erp_token','total_requested_amount','total_amount_paid','total_remaining_amount','created_at']
	    )->where('driver_erp_token = "'.$driverErpId.'"');
	    $data =  $connection->fetchAll($query);  
            $totalRequestedAmount = $data[0]["total_requested_amount"] ?? 0;
            $totalAmountPaid = $data[0]["total_amount_paid"] ?? 0;
            $totalRemainingAmount = $data[0]["total_remaining_amount"] ?? 0;
            $lastUpdatedAt = $data[0]["created_at"] ?? 0;
        }else{
  	    $totalRequestedAmount = 0;
            $totalAmountPaid = 0;
            $totalRemainingAmount = 0;
            $lastUpdatedAt = 0;	
	  }	
	    $deliveryboy = $this->deliveryboy->load($deliveryBoyId);
	    $noCoversDelivered = $deliveryboy->getNoOfCoversDelivered();
	    $noBoxesDelivered = $deliveryboy->getNoOfBoxesDelivered();
	    $no_of_orders = ($noCoversDelivered + $noBoxesDelivered);
	    $totalAmount = $deliveryboy->getTotalAmountToBeReceived() ?? 0;
	    $driverType = $deliveryboy->getDriverType();

	    return ['no_of_orders' => $no_of_orders, 'no_of_covers' => $noCoversDelivered, 'no_of_boxes' => $noBoxesDelivered, 'delivery_amount' => $totalAmount,'driver_type' => $driverType, 'total_requested_amount' => $totalRequestedAmount, 'total_amount_paid' => $totalAmountPaid, 'total_remaining_amount' => $totalRemainingAmount, 'created_at' => $lastUpdatedAt];
}

   /* public function deliveryOrdersDetails($deliveryBoyId)
    {
        $currDay = date("d");
        $curryear = date("Y");
        $currMonth = date("m");
        $to = $curryear . "-" . $currMonth . "-" . $currDay . " 23:59:59";
        $from = $curryear . "-" . $currMonth . "-" . $currDay . " 00:00:00";
        $tableName = $this->resource->getTableName("sales_order_grid");
        $collection = $this->deliveryboyOrderResourceCollection->create()
            ->join(
                [
                    "salesOrder" => $tableName
                ],
                "main_table.order_id=salesOrder.entity_id",
                [
                    "created_at" => "created_at"
                ]
            );
        $collection->addFieldToFilter(
            "created_at",
            [
                "to" => $to,
                "from" => $from,
                "datetime" => true
            ]
        )->addFieldToFilter("order_status",['eq' => 'order_delivered'])
        ->addFieldToFilter("deliveryboy_id",['eq' => $deliveryBoyId]);
        $noofcovers = $noofboxes = $totalDeliveryAmount = [];
        foreach($collection as $order) {
            if ($order->getPackageItems()) {
                $noofcovers[] = intval(explode(',', $order->getPackageItems())[0]);
                $noofboxes[] = intval(explode(',', $order->getPackageItems())[1]);
            }
            $totalDeliveryAmount[] = intval($order->getDeliveryAmount());
        }
        return ['no_of_orders' => $collection->getSize(), 'no_of_covers' => array_sum($noofcovers), 'no_of_boxes' => array_sum($noofboxes), 'delivery_amount' => array_sum($totalDeliveryAmount)];
   } */

    /**
     * GEt Order Count.
     *
     * @param int $deliveryboyId
     * @return int
     */
    public function getOrderCount(int $deliveryboyId): int
    {
        $tableName  = $this->resource->getTableName("sales_order_grid");
        $assignedOrderCollection = $this->deliveryboyOrderResourceCollection
            ->create()
            ->addFieldToFilter("deliveryboy_id", $deliveryboyId);
        $this->applyIntermediateFilterOrderCount($assignedOrderCollection);
        $assignedOrderCollection->getSelect()
            ->join(
                [
                    "salesOrder" => $tableName
                ],
                "main_table.order_id=salesOrder.entity_id AND main_table.order_status != 'complete'",
                []
            );
        return $assignedOrderCollection->getSize();
    }

    /**
     * Get Daily Order List.
     *
     * @return void
     */
    public function getDailyOrderList(): void
    {
        $currDay = date("d");
        $curryear = date("Y");
        $currMonth = date("m");
        $to = $curryear . "-" . $currMonth . "-" . $currDay . " 23:59:59";
        $from = $curryear . "-" . $currMonth . "-" . $currDay . " 00:00:00";
        $this->returnArray["dailyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * Get Weekly order list.
     *
     * @return void
     */
    public function getWeeklyOrderList(): void
    {
        $curryear = date("Y");
        $currMonth = date("m");
        $currDay = date("d");
        $currWeekDay = date("N");
        $startDate = strtotime("-" . ($currWeekDay-1) . " days", time());
        $prevyear = date("Y", $startDate);
        $prevMonth = date("m", $startDate);
        $prevDay = date("d", $startDate);
        $to = $curryear . "-" . $currMonth . "-" . $currDay . " 23:59:59";
        $from = $prevyear . "-" . $prevMonth . "-" . $prevDay . " 00:00:00";
        $this->returnArray["weeklyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * Get Monthrly ORder List.
     *
     * @return void
     */
    public function getMonthlyOrderList(): void
    {
        $currDay = date("d");
        $curryear = date("Y");
        $currMonth = date("m");
        $to = $curryear . "-" . $currMonth . "-" . $currDay . " 23:59:59";
        $from = $curryear . "-" . $currMonth . "-01 00:00:00";
        $this->returnArray["monthlyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * GEt Yearly ORder List.
     *
     * @return void
     */
    public function getYearlyOrderList(): void
    {
        $curryear = date("Y");
        $to = $curryear . "-12-31 23:59:59";
        $from = $curryear . "-01-01 00:00:00";
        $this->returnArray["yearlyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * Filter Collection.
     *
     * @param string $to
     * @param string $from
     * @return array
     */
    public function filterCollection(string $to, string $from): array
    {
        $tableName = $this->resource->getTableName("sales_order_grid");
        $collection = $this->deliveryboyOrderResourceCollection->create()
            ->join(
                [
                    "salesOrder" => $tableName
                ],
                "main_table.order_id=salesOrder.entity_id",
                [
                    "created_at" => "created_at",
                    "grand_total" => "grand_total",
                    "status" => "status"
                ]
            );
        $collection->addFieldToFilter(
            "created_at",
            [
                "to" => $to,
                "from" => $from,
                "datetime" => true
            ]
        );
        $this->applyIntermediateFilter($collection);
        $orderList = [];
        $mageOrderNew = \Magento\Sales\Model\Order::STATE_NEW;
        foreach ($collection as $eachOrder) {
            $oneOrder = [];
            $oneOrder["id"] = $eachOrder->getId();
            $oneOrder["dateTime"] = $eachOrder->getCreatedAt();
            $oneOrder["grandTotal"] = $eachOrder->getGrandTotal();
            $dbOrderState = $eachOrder->getOrderStatus() === $mageOrderNew
                    ? self::MAGE_STATUS_PENDING
                    : $eachOrder->getOrderStatus();
            $oneOrder["status"] = ucfirst($dbOrderState);
            $orderList[] = $oneOrder;
        }
        return $orderList;
    }

    /**
     * Get Order Status.
     *
     * @return array
     */
    protected function getOrderStatus(): array
    {
        return array_values(
            $statusCollection = $this->orderStatusCollection
                ->getResourceCollection()
                ->getData()
        );
    }

    /**
     * Filter Order Collection.
     *
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function applyIntermediateFilter(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
    ) : \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection {
        if ($this->isDeliveryboy()) {
            $collection->addFieldToFilter("deliveryboy_id", ['eq' => $this->userId]);
        }

        return $collection;
    }

    /**
     * Validate Request Data.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function validateRequestData()
    {
        if (!(
        $this->isDeliveryboy()
        || $this->isAdmin())
        ) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * Filter Deliveryboy Collection.
     *
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function applyFiltersDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {

        return $collection;
    }

    /**
     * Is Deliveryboy.
     *
     * @return bool
     */
    public function isDeliveryboy(): bool
    {
        return ($this->isDeliveryboy && ($this->userId > 0));
    }

    /**
     * Is Admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * Extract Deliveyboy Data.
     *
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @return array
     */
    protected function extractDeliveryboyNecessaryFileldsFromDeliveryboyObject(
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
    ): array {
        $return = [
            "name" => $deliveryboy->getName(),
            "status" => (bool)$deliveryboy->getAvailabilityStatus(),
            "mobile" => $deliveryboy->getMobileNumber(),
            "latitude" => $deliveryboy->getLatitude(),
            "longitude" => $deliveryboy->getLongitude(),
            "orderCount" => $this->getOrderCount($deliveryboy->getId())
        ];
        return array_merge($return, $this->deliveryOrdersDetails($deliveryboy->getId(), $deliveryboy->getDriverErpId()));
    }

    /**
     * Filter Dliveryboy order.
     *
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function applyIntermediateFilterOrderCount(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
    ) : \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection {

        return $collection;
    }
}
