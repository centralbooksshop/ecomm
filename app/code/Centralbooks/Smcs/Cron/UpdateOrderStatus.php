<?php
namespace Centralbooks\Smcs\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Centralbooks\Smcs\Helper\Api;
use Centralbooks\Smcs\Model\TokenFactory;

class UpdateOrderStatus
{
    protected $collectionFactory;
    protected $orderRepository;
    protected $convertOrder;
    protected $trackFactory;
    protected $shipmentNotifier;
    protected $logger;
    protected $api;
    protected $tokenFactory;
    protected $customLogger;

    public function __construct(
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        ConvertOrder $convertOrder,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier,
        LoggerInterface $logger,
        Api $api,
        TokenFactory $tokenFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository   = $orderRepository;
        $this->convertOrder      = $convertOrder;
        $this->trackFactory      = $trackFactory;
        $this->shipmentNotifier  = $shipmentNotifier;
        $this->logger            = $logger;
        $this->api               = $api;
        $this->tokenFactory      = $tokenFactory;

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/smcs.log');
        $this->customLogger = new \Zend_Log();
        $this->customLogger->addWriter($writer);
    }

    public function execute()
    {
        $this->customLogger->info("SMCS UpdateOrderStatus Cron Started");

        try {

            $token = $this->getValidToken();

            $orders = $this->collectionFactory->create()
                ->addFieldToFilter('cbo_courier_name', 'SMCS')
                ->addFieldToFilter('status', ['nin' => ['complete', 'canceled']]);

            foreach ($orders as $order) {

                try {

                    $reference = $order->getIncrementId();

                    $payload = [
                        "data" => [
                            "reference_no" => $reference,
                            "type"         => "delivery"
                        ]
                    ];

					$this->customLogger->info('SMCS client_tracking payload: ' . print_r($payload, true));
					

                    $this->customLogger->info(
                        'SMCS client_tracking Payload: ' . json_encode($payload)
                    );

                    $response = $this->api->call('/client_tracking', $payload, $token);

                    $this->customLogger->info(
                        'SMCS client_tracking Response: ' . json_encode($response)
                    );
					$this->customLogger->info('SMCS client_tracking Response: ' . print_r($response, true));

                    if (empty($response['success']) || empty($response['data'])) {
                        continue;
                    }

                    $trackingInfo = $response['data']['trackinginfo'] ?? [];

                    /* ================================
                       STEP 1: CREATE SHIPMENT
                    ================================ */

                    if (!empty($trackingInfo['DocumentNo']) && !$order->hasShipments()) {

                        $awb = $trackingInfo['DocumentNo'];

                        if ($order->canShip()) {

                            $this->createShipment($order, $awb);

                            $this->customLogger->info(
                                "Shipment created for Order {$reference}"
                            );
                        }
                    }

                    /* ================================
                       STEP 2: CHECK DELIVERY STATUS
                    ================================ */

                    $deliveryStatus = strtolower(
                        $trackingInfo['DeliveryStatus'] ?? ''
                    );

                    if ($deliveryStatus === 'delivered') {

                        if ($order->getState() !== Order::STATE_COMPLETE) {

                            $order->setState(Order::STATE_COMPLETE)
                                ->setStatus('order_delivered')
                                ->addStatusHistoryComment(
                                    'Order marked as Delivered via SMCS Cron'
                                );

                            $this->orderRepository->save($order);

                            $this->customLogger->info(
                                "Order {$reference} marked Delivered"
                            );
                        }
                    }

                } catch (\Exception $e) {

                    $this->customLogger->err(
                        "Order {$order->getIncrementId()} Error: " . $e->getMessage()
                    );
                }
            }

        } catch (\Exception $e) {

            $this->customLogger->err("SMCS Cron Error: " . $e->getMessage());
        }

        $this->customLogger->info("SMCS UpdateOrderStatus Cron Completed");
    }

    /* =====================================
       CREATE SHIPMENT
    ===================================== */

    protected function createShipment($order, $awb)
    {
        $shipment = $this->convertOrder->toShipment($order);

        foreach ($order->getAllItems() as $item) {

            if (!$item->getQtyToShip() || $item->getIsVirtual()) {
                continue;
            }

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
            $this->customLogger->warn(
                "Shipment email failed: " . $e->getMessage()
            );
        }
    }

    /* =====================================
       TOKEN HANDLING
    ===================================== */

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
}