<?php
namespace Centralbooks\Elasticrun\Controller\Adminhtml\Bulkship;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Centralbooks\Elasticrun\Helper\Data;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $messageManager;
    protected $urlInterface;
    protected $scopeConfig;
    protected $dataHelper;
    protected $orderRepository;
    protected $convertOrder;
    protected $trackFactory;
    protected $shipmentNotifier;
    protected $logger;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        UrlInterface $urlInterface,
        ScopeConfigInterface $scopeConfig,
        Data $dataHelper,
        OrderRepositoryInterface $orderRepository,
        ConvertOrder $convertOrder,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->messageManager  = $messageManager;
        $this->urlInterface    = $urlInterface;
        $this->scopeConfig     = $scopeConfig;
        $this->dataHelper      = $dataHelper;
        $this->orderRepository = $orderRepository;
        $this->convertOrder    = $convertOrder;
        $this->trackFactory    = $trackFactory;
        $this->shipmentNotifier= $shipmentNotifier;
        $this->logger          = $logger;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $selected = $this->getRequest()->getParam('selected');

            if (empty($selected) || !is_array($selected)) {
                throw new \Exception('No orders selected for shipping.');
            }

            // We'll allow multiple selected orders; process each separately.
            $successCount = 0;
            $failures = [];

            // Load config once
            $apiUrl           = trim($this->scopeConfig->getValue('elasticrun_configuration/general/api_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $apiToken         = trim($this->scopeConfig->getValue('elasticrun_configuration/general/api_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $orgId            = trim($this->scopeConfig->getValue('elasticrun_configuration/general/org_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $userId           = trim($this->scopeConfig->getValue('elasticrun_configuration/general/user_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $consignorName    = trim($this->scopeConfig->getValue('elasticrun_configuration/general/consignor_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $consignorContact = trim($this->scopeConfig->getValue('elasticrun_configuration/general/consignor_contact', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $consignorEmail   = trim($this->scopeConfig->getValue('elasticrun_configuration/general/consignor_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $originCity       = trim($this->scopeConfig->getValue('elasticrun_configuration/general/origin_city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $pickupAddressRaw = $this->scopeConfig->getValue('elasticrun_configuration/general/pickup_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $shipperAddressRaw = $this->scopeConfig->getValue('elasticrun_configuration/general/shipper_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

			$defaultWeightKg = (float)$this->scopeConfig->getValue('elasticrun_configuration/general/default_weight',\Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 0.5;

            if (empty($apiUrl) || empty($apiToken)) {
                throw new \Exception('Elasticrun API URL or token not configured.');
            }

            foreach ($selected as $orderId) {
                try {
                    $orderId = (int)$orderId;
                    $order = $this->orderRepository->get($orderId);

                    if (!$order || !$order->getId()) {
                        $failures[] = "Order ID {$orderId}: Order not found.";
                        continue;
                    }

                    // Only allow assigned_to_picker status (same restriction you had)
                    if ($order->getStatus() !== 'assigned_to_picker') {
                        $failures[] = "Order {$order->getIncrementId()}: status '{$order->getStatus()}' not allowed.";
                        continue;
                    }

                    // Skip if already shipped by Elasticrun (cbo_reference_number or courier name)
                    if (!empty($order->getData('cbo_reference_number')) || strtolower($order->getData('cbo_courier_name') ?? '') === 'elasticrun') {
                        $failures[] = "Order {$order->getIncrementId()}: Already shipped/assigned (cbo_reference_number present).";
                        continue;
                    }

                    // Prepare order data
                    $shipping = $order->getShippingAddress() ? $order->getShippingAddress()->getData() : [];
                    $billing  = $order->getBillingAddress() ? $order->getBillingAddress()->getData() : [];
                    $payment  = $order->getPayment();
                    $codAmount = ($payment && $payment->getMethod() === 'cashondelivery') ? $order->getTotalDue() : 0;

                    // street normalization
                    $street = '';
                    if (!empty($shipping['street'])) {
                        if (is_array($shipping['street'])) {
                            $street = implode(', ', $shipping['street']);
                        } else {
                            $street = (string)$shipping['street'];
                        }
                    }

                    // parse pickup/shipper config (graceful)
                    $pickupAddress = $this->normalizeAddressFromConfig($pickupAddressRaw);
                    $shipperAddress = $this->normalizeAddressFromConfig($shipperAddressRaw);

					$totalWeightKg = 0.0;

					foreach ($order->getAllVisibleItems() as $item) {
						$qty = (int)$item->getQtyOrdered();

						// Magento stores weight in KG
						$itemWeightKg = (float)$item->getWeight();

						// fallback if weight missing
						if ($itemWeightKg <= 0) {
							$itemWeightKg = $defaultWeightKg;
						}

						$totalWeightKg += ($itemWeightKg * $qty);
					}

					// Final safety fallback
					if ($totalWeightKg <= 0) {
						$totalWeightKg = $defaultWeightKg;
					}

					// round to 3 decimals (Elasticrun safe)
					$totalWeightKg = round($totalWeightKg, 3);


                    $payload = [
                        "data" => [
                            "consignor_name" => $consignorName ?: ($pickupAddress['name'] ?? 'CBS HUB'),
                            "consignor_contact_no" => $consignorContact ?: ($pickupAddress['contact_no'] ?? ''),
                            "consignor_email" => $consignorEmail ?: '',
                            "origin_city" => $originCity ?: ($pickupAddress['city'] ?? ''),
                            "consignee_name" => trim(($shipping['firstname'] ?? '') . ' ' . ($shipping['lastname'] ?? '')),
                            "consignee_contact_no" => $shipping['telephone'] ?? '',
                            "consignee_email" => $billing['email'] ?? 'no.reply@nomail.com',
                            "destination_city" => $shipping['city'] ?? '',
                            "is_first_mile_pickup" => 1,
                            "is_last_mile_pickup" => 0,
                            "is_seller_return" => 0,
                            "total_volume" => 0.02,
                            "weight" => $totalWeightKg,
                            "payment_method" => $codAmount > 0 ? "cod" : "prepay",
                            "amount_to_collect" => (float)$codAmount,
                            "order_amount" => (float)$order->getGrandTotal(),
                            "user_id" => $userId ?: 'CB',
                            "org_id" => $orgId ?: 'CB',
                            "addresses" => [
                                [
                                    "name" => trim(($shipping['firstname'] ?? '') . ' ' . ($shipping['lastname'] ?? '')),
                                    "address_type" => "Consignee Address",
                                    "address" => $street,
                                    "city" => $shipping['city'] ?? '',
                                    "state" => $shipping['region'] ?? '',
                                    "country" => $shipping['country_id'] ?? 'IN',
                                    "postal" => $shipping['postcode'] ?? '',
                                    "lat_long" => "",
                                    "landmark" => $shipping['region'] ?? ""
                                ],
                                $pickupAddress,
                                $shipperAddress
                            ],
                            "shipments" => [
                                [
                                    "shipper_ref_no" => $order->getIncrementId(),
                                    "weight" => $totalWeightKg,
                                    "destination_city" => $shipping['city'] ?? '',
                                    "origin_city" => $originCity ?: ($pickupAddress['city'] ?? ''),
                                    "is_first_mile_pickup" => 1,
                                    "is_last_mile_pickup" => 0,
                                    "height" => 1,
                                    "width" => 1,
                                    "length" => 1,
                                    "shipping_state" => "Pickup",
                                    "addresses" => [
                                        [
                                            "name" => trim(($shipping['firstname'] ?? '') . ' ' . ($shipping['lastname'] ?? '')),
                                            "address_type" => "Consignee Address",
                                            "address" => $street,
                                            "city" => $shipping['city'] ?? '',
                                            "state" => $shipping['region'] ?? '',
                                            "country" => $shipping['country_id'] ?? 'IN',
                                            "postal" => $shipping['postcode'] ?? '',
                                            "lat_long" => "",
                                            "landmark" => $shipping['region'] ?? ""
                                        ],
                                        $pickupAddress,
                                        $shipperAddress
                                    ],
                                ]
                            ],
                            "items" => []
                        ]
                    ];

                    foreach ($order->getAllVisibleItems() as $item) {

						$itemWeightKg = (float)$item->getWeight();

						// fallback if weight missing
						if ($itemWeightKg <= 0) {
							$itemWeightKg = $defaultWeightKg;
						}

						$payload['data']['items'][] = [
							"item_count" => (string)$item->getQtyOrdered(),
							"item_id" => $item->getSku(),
							"item_name" => $item->getName(),
							"procured_quantity" => (int)$item->getQtyOrdered(),
							"per_unit_cost" => (float)$item->getPrice(),
							"discount" => (float)$item->getDiscountAmount(),
							"amount_after_discount" => (float)($item->getRowTotalInclTax() - $item->getDiscountAmount()),
							"weight" => round($itemWeightKg, 3) // KG
						];
					}


                    // send API request
                    $this->logger->debug('Elasticrun payload (order ' . $order->getIncrementId() . '): ' . json_encode($payload));
                    list($success, $responseOrMsg) = $this->sendElasticrunRequest($apiUrl, $apiToken, $payload);

                    if (!$success) {
                        $failures[] = "Order {$order->getIncrementId()}: API error: {$responseOrMsg}";
                        continue;
                    }

                    $decoded = $responseOrMsg;

					// Check Elasticrun business status (green/red)
					$apiStatus  = $decoded['message']['status']  ?? null;
					$apiMessage = $decoded['message']['message'] ?? null;

					if ($apiStatus !== 'green') {
						$msg = $apiMessage ?: 'Elasticrun returned an error.';
						$failures[] = "Order {$order->getIncrementId()}: {$msg}";
						$this->logger->error('Elasticrun error for order ' . $order->getIncrementId() . ': ' . $msg);
						continue;
					}

					// Extract response data safely (success case)
					$responseData = null;
					if (isset($decoded['message']['data'][0]['data'])) {
						$responseData = $decoded['message']['data'][0]['data'];
					} elseif (isset($decoded['data'][0])) {
						$responseData = $decoded['data'][0];
					} elseif (isset($decoded['data'])) {
						$responseData = $decoded['data'];
					} elseif (isset($decoded['message']) && is_array($decoded['message'])) {
						$responseData = $decoded['message'];
					}

					if (!is_array($responseData)) {
						$failures[] = "Order {$order->getIncrementId()}: Invalid response from Elasticrun (no data block).";
						$this->logger->error('Elasticrun invalid response for order ' . $order->getIncrementId() . ': ' . json_encode($decoded));
						continue;
					}

					$trackingId = $responseData['tracking_id']
						?? $responseData['consignment_id']
						?? $responseData['shipment_id']
						?? null;

					$labelUrl = $responseData['consignment_document'][0]['document_file']
						?? ($responseData['label_url'] ?? null);


                    if (empty($trackingId)) {
                        // still save raw response if no trackingId
                        $order->setData('cbo_courier_name', 'Elasticrun');
                        $order->setData('cbo_reference_number', json_encode($responseData));
                        $this->orderRepository->save($order);
                        $failures[] = "Order {$order->getIncrementId()}: No tracking id returned (saved response to order).";
                        continue;
                    }

                    // save tracking info to order before creating shipment
                    $order->setData('cbo_courier_name', 'Elasticrun');
                    $order->setData('cbo_reference_number', $trackingId);
                    if ($labelUrl) {
                        $order->setData('elasticrun_label_url', $labelUrl);
                    }

                    $this->orderRepository->save($order);

                    // Create shipment & add track
                    if ($order->canShip()) {
                        $shipment = $this->convertOrder->toShipment($order);
                        foreach ($order->getAllItems() as $item) {
                            if (!$item->getQtyToShip() || $item->getIsVirtual()) continue;
                            $shipmentItem = $this->convertOrder->itemToShipmentItem($item)->setQty($item->getQtyToShip());
                            $shipment->addItem($shipmentItem);
                        }
                        $shipment->register();
                        $shipment->getOrder()->setIsInProcess(true);

                        $trackData = [
                            'carrier_code' => 'elasticrun',
                            'title' => 'Elasticrun',
                            'number' => $trackingId
                        ];
                        $track = $this->trackFactory->create()->addData($trackData);
                        $shipment->addTrack($track);
                        $shipment->save();
                        $shipment->getOrder()->save();

                        // notify customer (best-effort)
                        try {
                            $this->shipmentNotifier->notify($shipment);
                        } catch (\Exception $ex) {
                            $this->logger->warning('Shipment notify failed for order ' . $order->getIncrementId() . ': ' . $ex->getMessage());
                        }
                    }

                    $successCount++;
                    $this->logger->info('Order ' . $order->getIncrementId() . ' shipped via Elasticrun. Tracking: ' . $trackingId);

                } catch (\Exception $e) {
                    $this->logger->error('Elasticrun processing error for order ' . (isset($order) ? $order->getIncrementId() : $orderId) . ': ' . $e->getMessage());
                    $failures[] = "Order {$orderId}: Exception - " . $e->getMessage();
                    continue;
                }
            } // end foreach selected

            // Present summary to admin
            if ($successCount > 0) {
                $this->messageManager->addSuccessMessage("Elasticrun shipments created for {$successCount} order(s).");
            }
            if (!empty($failures)) {
                // show up to first 7 failures
                $first = array_slice($failures, 0, 7);
                $failureText = implode('<br/>', $first);
                $this->messageManager->addErrorMessage(__('Some orders failed: <br/>%1', $failureText));
            }

            return $resultRedirect->setPath('sales/order/index');

        } catch (\Exception $e) {
            $this->logger->error('Elasticrun bulk error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
            return $resultRedirect->setPath('sales/order/index');
        }
    }

    /**
     * Send Elasticrun API request and return [bool success, array|message]
     * @return array [bool, mixed] success=true & array decoded response, success=false & string error
     */
    protected function sendElasticrunRequest($apiUrl, $apiToken, $payload)
    {
        $headers = [
            "Authorization: {$apiToken}",
            "Content-Type: application/json"
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            return [false, 'CURL Error: ' . $curlError];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [false, "HTTP {$httpCode}: " . substr($response, 0, 2048)];
        }

        $decoded = json_decode($response, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return [false, 'Invalid JSON response'];
        }

        return [true, $decoded];
    }

    /**
     * Normalize pickup/shipper addresses configured in admin (JSON or simple array/object)
     */
    protected function normalizeAddressFromConfig($raw)
    {
        $default = [
            "name" => "CBS HUB PRIVATE LIMITED",
            "address_type" => "Consignor Address",
            "address" => "Plot No A 28/1/B, Road No 15,I.D.A",
            "city" => "Nacharam",
            "state" => "Telangana",
            "country" => "IN",
            "postal" => "500040",
            "lat_long" => "",
            "landmark" => ""
        ];

        if (empty($raw)) {
            return $default;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // If the admin saved array-of-objects, take first
            if (!empty($decoded[0]) && is_array($decoded[0])) {
                return array_merge($default, $decoded[0]);
            }
            // Single object
            if (isset($decoded['address']) || isset($decoded['city']) || isset($decoded['name'])) {
                return array_merge($default, $decoded);
            }
        }

        // fallback if raw isn't JSON or decode failed
        return $default;
    }
}
