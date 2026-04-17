<?php
namespace Centralbooks\Smcs\Controller\Adminhtml\Bulkship;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Psr\Log\LoggerInterface;
use Centralbooks\Smcs\Helper\Api;
use Centralbooks\Smcs\Model\TokenFactory;

class Index extends Action
{
    protected $filter;
    protected $collectionFactory;
    protected $orderRepository;
    protected $convertOrder;
    protected $trackFactory;
    protected $shipmentNotifier;
    protected $logger;
    protected $api;
    protected $tokenFactory;
    protected $config;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        ConvertOrder $convertOrder,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier,
        LoggerInterface $logger,
        Api $api,
        TokenFactory $tokenFactory,
        \Centralbooks\Smcs\Helper\Config $config
    ) {
        parent::__construct($context);

        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository   = $orderRepository;
        $this->convertOrder      = $convertOrder;
        $this->trackFactory      = $trackFactory;
        $this->shipmentNotifier  = $shipmentNotifier;
        $this->customLogger            = $logger;
        $this->api               = $api;
        $this->tokenFactory      = $tokenFactory;
        $this->config            = $config;
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        try {

            $collection = $this->filter->getCollection(
                $this->collectionFactory->create()
            );

            if (!$collection->getSize()) {
                throw new \Exception("No orders selected.");
            }

            $token = $this->getValidToken();

            $success = 0;
            $failures = [];

            foreach ($collection as $order) {

                try {

                    if (!$order->canShip()) {
                        $failures[] = "Order {$order->getIncrementId()} cannot be shipped.";
                        continue;
                    }

					if ($order->getStatus() !== "assigned_to_picker") {
                        $failures[] = "Order {$order->getIncrementId()} has invalid status.";
                        continue;
                    }

                    $shipping = $order->getShippingAddress();

                    /* ===============================
                       STEP 1 — INSERT BOOKING
                    ================================ */

                    $payload = $this->buildInsertPayload($order, $shipping);

                    $this->customLogger->info(
                        'SMCS insertbooking Payload: ' . json_encode($payload)
                    );

                    $insertResponse = $this->api->call(
                        '/insertbooking',
                        $payload,
                        $token
                    );

                    $this->customLogger->info(
                        'SMCS insertbooking Response: ' . json_encode($insertResponse)
                    );

                    if (empty($insertResponse['success'])) {

						// If booking already exists, continue to fetch AWB
						if (isset($insertResponse['message']) &&
							strpos($insertResponse['message'], 'already exist') !== false) {

							$this->customLogger->info(
								"Booking already exists. Fetching AWB for order "
								. $order->getIncrementId()
							);

						} else {
							$failures[] = "Order {$order->getIncrementId()} booking failed.";
							continue;
						}
					}

                    /*sleep(2); // allow SMCS to process

                    $awb = $this->fetchAwb($order, $token);

                    if (!$awb) {
                        $failures[] = "Order {$order->getIncrementId()} - AWB not generated.";
                        continue;
                    }*/
                    sleep(2); // allow SMCS to process

					//$awb = $this->fetchAwb($order, $token);
					$awb = $order->getIncrementId();

					if (!$awb) {
						$failures[] = "Order {$order->getIncrementId()} - AWB not generated.";
						continue;
					}

                    $order->setData('cbo_courier_name', 'SMCS');
                    $order->setData('cbo_reference_number', $awb);
                    $this->orderRepository->save($order);

                    $this->createShipment($order, $awb);

                    $success++;

                } catch (\Exception $e) {
                    $failures[] = "Order {$order->getIncrementId()}: " . $e->getMessage();
                    $this->customLogger->error($e->getMessage());
                }
            }

            if ($success) {
                $this->messageManager->addSuccessMessage(
                    __("SMCS shipments created for %1 order(s).", $success)
                );
            }

            if (!empty($failures)) {
                $this->messageManager->addErrorMessage(
                    implode("<br>", $failures)
                );
            }

            return $redirect->setPath("sales/order/index");

        } catch (\Exception $e) {
            $this->customLogger->error($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            return $redirect->setPath("sales/order/index");
        }
    }

    /* =====================================================
       TOKEN HANDLING
    ====================================================== */

    protected function getValidToken()
    {
        $tokenModel = $this->tokenFactory->create()->load(1);

        if (!$tokenModel->getAuthToken()
            || strtotime($tokenModel->getExpiresAt()) <= time()) {

            $login = $this->api->login();

            if (empty($login['success'])) {
                throw new \Exception("Unable to refresh SMCS token.");
            }

            $tokenModel->setData([
                'entity_id'  => 1,
                'auth_token' => $login['AuthToken'],
                'expires_at' => $login['TokenExpiredOn'],
                'is_dp'      => $login['data']['IsDP'],
                'user_id'    => $login['data']['UserID']
            ])->save();
        }

        return $tokenModel->getAuthToken();
    }

    /* =====================================================
       BUILD PAYLOAD
    ====================================================== */

    protected function buildInsertPayload($order, $shipping)
    {
        $weight = 0;

        foreach ($order->getAllVisibleItems() as $item) {
            $itemWeight = $item->getWeight() ?: 0.05; // fallback 50g
            $weight += ($itemWeight * 1000);
        }

        $weight = max(50, (int)$weight);

        return [
            "Data" => [[
                "data" => [
                    "ClientRefID"   => $this->config->getClientCode(),
                    "IsDP"          => 1,
                    "DocumentNoRef" => $order->getIncrementId(),
                    "PickupPincode" => $this->config->getPickupPincode(),
                    "ToPincode"     => $shipping->getPostcode(),
                    "CodBooking"    => $order->getPayment()->getMethod() == "cashondelivery" ? "1" : "0",
                    "TypeID"        => 2,
                    "ServiceTypeID" => 1,
                    "TravelBy"      => 2,
                    "Weight"        => $weight,
                    "ValueRs"       => round($order->getGrandTotal(), 2),
                    "ReceiverName"  => $shipping->getName(),
                    "ReceiverAddress" => substr($shipping->getStreetLine(1), 0, 200),
                    "ReceiverCity"  => $shipping->getCity(),
                    "ReceiverState" => 1,
                    "Area"          => substr($shipping->getStreetLine(1), 0, 100),
                    "ReceiverMobile"=> substr($shipping->getTelephone(), 0, 10),
                    "ReceiverEmail" => $order->getCustomerEmail(),
                    "Remarks"       => "Magento Order",
                    "UserID"        => 139390
                ]
            ]]
        ];
    }

    /* =====================================================
       FETCH AWB
    ====================================================== */

    protected function fetchAwb($order, $token)
	{
		$payload = [
			"data" => [
				"reference_no" => $order->getIncrementId()
			]
		];

		$this->customLogger->info(
			'SMCS client_tracking_all Payload: ' . json_encode($payload)
		);

		$response = $this->api->call('/client_tracking_all', $payload, $token);


		$this->customLogger->info(
			'SMCS client_tracking_all Response: ' . json_encode($response)
		);
		//print_r($response);die;

		if (empty($response['success']) || empty($response['data']['bookinginfo'])) {
			return null;
		}

		return $response['data']['bookinginfo']['DocumentNo']
			?? null;
	}

    /* =====================================================
       CREATE SHIPMENT
    ====================================================== */

    protected function createShipment($order, $awb)
    {
        $shipment = $this->convertOrder->toShipment($order);

        foreach ($order->getAllItems() as $item) {
            if (!$item->getQtyToShip() || $item->getIsVirtual()) continue;

            $shipmentItem = $this->convertOrder
                ->itemToShipmentItem($item)
                ->setQty($item->getQtyToShip());

            $shipment->addItem($shipmentItem);
        }

        $track = $this->trackFactory->create()->addData([
            "carrier_code" => "smcs",
            "title"        => "Shree Maruti Courier",
            "number"       => $awb
        ]);

        $shipment->addTrack($track);

        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);

        $shipment->save();
        $shipment->getOrder()->save();

        try {
            $this->shipmentNotifier->notify($shipment);
        } catch (\Exception $e) {
            $this->customLogger->warning("Shipment email failed: " . $e->getMessage());
        }
    }
}