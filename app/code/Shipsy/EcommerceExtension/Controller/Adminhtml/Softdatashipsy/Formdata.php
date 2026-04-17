<?php

namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Softdatashipsy;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Shipsy\EcommerceExtension\Helper\Data;
use Psr\Log\LoggerInterface;

class Formdata extends Action
{
    protected $messageManager;
    protected $urlInterface;
    protected $cookieManager;
    protected $scopeConfig;
    protected $dataHelper;
    protected $orderRepository;
    protected $convertOrder;
    protected $trackFactory;
    protected $shipmentNotifier;
    protected $logger;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        UrlInterface $urlInterface,
        CookieManagerInterface $cookieManager,
        ScopeConfigInterface $scopeConfig,
        Data $dataHelper,
        OrderRepositoryInterface $orderRepository,
        ConvertOrder $convertOrder,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
        $this->orderRepository = $orderRepository;
        $this->convertOrder = $convertOrder;
        $this->trackFactory = $trackFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->logger = $logger;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    public function execute()
    {
        
		$defaultWeightKg = (float)$this->scopeConfig->getValue('configuration/services/default_weight',\Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 0.5;

		$originConfigJson = $this->scopeConfig->getValue(
			'configuration/services/ship_from_json',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		$MumbaioriginConfigJson = $this->scopeConfig->getValue(
			'configuration/services/ship_from_mumbai_json',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		
		$selected = $this->getRequest()->getParam('selected');
        $resultRedirect = $this->resultRedirectFactory->create();
        $success = [];
        $failed = [];

        if (empty($selected) || !is_array($selected)) {
            $this->messageManager->addErrorMessage(__('No orders selected for sync.'));
            return $resultRedirect->setPath('sales/order/index');
        }

        foreach ($selected as $orderId) {
            try {
                $order = $this->orderRepository->get($orderId);

                if (!$order || !$order->getId()) {
                    $failed[] = sprintf('#%s: Order not found', $orderId);
                    continue;
                }

				$schoolId = (int)$order->getData('school_id');

				$pickupRegion = 1;

				if ($schoolId > 0) {

					$resource = \Magento\Framework\App\ObjectManager::getInstance()
						->get(\Magento\Framework\App\ResourceConnection::class);

					$connection  = $resource->getConnection();
					$schoolTable = $resource->getTableName('schools_registered');

					$pickupRegionDb = $connection->fetchOne(
						"SELECT dtdc_pickup_region FROM {$schoolTable} WHERE school_name = ? LIMIT 1",
						[$schoolId]
					);

					if ($pickupRegionDb !== false && $pickupRegionDb !== null) {
						$pickupRegion = (int)$pickupRegionDb;
					}
				}

				$originDetails = [];

				$selectedOriginJson = ($pickupRegion === 2)
					? $MumbaioriginConfigJson
					: $originConfigJson;

				if (!empty($selectedOriginJson)) {

					$decodedOrigin = json_decode($selectedOriginJson, true);

					if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOrigin)) {

						$originDetails = [
							'name'           => $decodedOrigin['name'] ?? '',
							'phone'          => $decodedOrigin['phone'] ?? '',
							'address_line_1' => $decodedOrigin['address_line_1'] ?? '',
							'pincode'        => $decodedOrigin['pincode'] ?? '',
							'city'           => $decodedOrigin['city'] ?? '',
							'state'          => $decodedOrigin['state'] ?? '',
							'country'        => $decodedOrigin['country'] ?? 'IN',
						];

					} else {
						$this->logger->error('Invalid JSON in ship_from config for region: ' . $pickupRegion);
					}
				}

				if (empty($originDetails)) {
					$failed[] = sprintf('#%s: Origin details missing (Region: %s)', $order->getIncrementId(), $pickupRegion);
					continue;
				}

                // Only allow assigned_to_picker status for sync
                if ($order->getStatus() !== 'assigned_to_picker') {
                    $failed[] = sprintf(
                        '#%s (%s): Invalid status "%s". Only orders with status "assigned_to_picker" can be synced.',
                        $order->getIncrementId(),
                        $orderId,
                        $order->getStatus()
                    );
                    continue;
                }

                // Skip if already synced
                if (!empty($order->getData('cbo_reference_number'))) {
                    $failed[] = sprintf('#%s: Already synced with Shipsy', $order->getIncrementId());
                    continue;
                }

                // Check if order has shipping address
                $shippingAddress = $order->getShippingAddress();
                if (!$shippingAddress) {
                    $failed[] = sprintf('#%s: Shipping address missing', $order->getIncrementId());
                    continue;
                }

                // Determine service code (default fallback)
                $serviceCode = $this->getDefaultServiceCode() ?: 'DTDC';

                // Payment / COD
                $payment = $order->getPayment();
                $codAmount = ($payment && $payment->getMethod() === 'cashondelivery') ? (float)$order->getTotalDue() : 0.0;
                $codMode = ($codAmount > 0) ? 'cash' : '';

                // Build payload per order
                $billingAddress = $order->getBillingAddress();

				$totalWeightKg = 0.0;

				foreach ($order->getAllVisibleItems() as $item) {
					$qty = (int)$item->getQtyOrdered();
					$itemWeightKg = (float)$item->getWeight(); // Magento stores KG

					// fallback if product weight missing
					if ($itemWeightKg <= 0) {
						$itemWeightKg = $defaultWeightKg;
					}

					$totalWeightKg += ($itemWeightKg * $qty);
				}

				// Final safety fallback
				if ($totalWeightKg <= 0) {
					$totalWeightKg = $defaultWeightKg;
				}

				// Shipsy-safe rounding
				$totalWeightKg = round($totalWeightKg, 3);

                $dataToSendArray = [
                    'consignments' => [
                        [
                            'customer_code' => $this->cookieManager->getCookie('customer-code'),
                            'consignment_type' => 'forward',
                            'service_type_id' => $serviceCode,
                            'load_type' => 'NON-DOCUMENT',
                            'customer_reference_number' => $order->getIncrementId(),
                            'num_pieces' => 1,
				            /*'origin_details' => [
                                'name' => trim(($billingAddress->getFirstname() ?? '') . ' ' . ($billingAddress->getLastname() ?? '')),
                                'phone' => $billingAddress->getTelephone(),
                                'address_line_1' => implode(' ', (array)$billingAddress->getStreet()),
                                'pincode' => $billingAddress->getPostcode(),
                                'city' => $billingAddress->getCity(),
                                'state' => $billingAddress->getRegion(),
                                'country' => $billingAddress->getCountryId(),
                            ],*/

					        'origin_details' => $originDetails,
                            'destination_details' => [
                                'name' => trim(($shippingAddress->getFirstname() ?? '') . ' ' . ($shippingAddress->getLastname() ?? '')),
                                'phone' => $shippingAddress->getTelephone(),
                                'address_line_1' => implode(' ', (array)$shippingAddress->getStreet()),
                                'pincode' => $shippingAddress->getPostcode(),
                                'city' => $shippingAddress->getCity(),
                                'state' => $shippingAddress->getRegion(),
                                'country' => $shippingAddress->getCountryId(),
                            ],
                            'same_pieces' => false,
                            'cod_collection_mode' => $codMode,
                            'cod_amount' => (float)$codAmount,
                            'cod_favor_of' => '',
                           'pieces_detail' => [
							[
								'description' => 'Order #' . $order->getIncrementId(),
								'declared_value' => (float)$order->getGrandTotal(),
								'quantity' => 1,
								'weight' => $totalWeightKg,
								'weight_unit' => 'kg',
								'height' => 1,
								'length' => 1,
								'width' => 1,
								'dimension_unit' => 'in'
							],
						],

                        ],
                    ],
                ];

				//echo '<pre>';print_r($dataToSendArray);die;

                // Send API request
                $response = $this->sendShipmentRequest($dataToSendArray);

                // Validate response
                $referenceNumber = $response['data'][0]['reference_number'] ?? null;
                if (empty($referenceNumber)) {
                    $errorMsg = $response['data'][0]['message'] ?? $response['error']['message'] ?? 'Invalid response from Shipsy API.';
                    $failed[] = sprintf('#%s: %s', $order->getIncrementId(), $errorMsg);
                    $this->logger->error("Shipsy API failed for Order {$order->getIncrementId()}: " . print_r($response, true));
                    continue;
                }

                // Update order fields
                $courier_name = 'DTDC';
                $order->setData('cbo_courier_name', $courier_name);
                $order->setData('cbo_reference_number', $referenceNumber);
                $order->setData('shipsy_cron_error_log', 'Synced');

                // Save order first (so order state is updated before creating shipment)
                $this->orderRepository->save($order);

                // Create Magento shipment if possible
                if (!$order->canShip()) {
                    // If cannot create shipment, still treat as success for sync but inform user
                    $success[] = sprintf('#%s: Synced (Ref: %s) — shipment not created (cannot create shipment)', $order->getIncrementId(), $referenceNumber);
                    $this->logger->info("Order {$order->getIncrementId()} synced but cannot create shipment.");
                    continue;
                }

                // Create shipment
                $shipment = $this->convertOrder->toShipment($order);
                foreach ($order->getAllItems() as $item) {
                    if (!$item->getQtyToShip() || $item->getIsVirtual()) {
                        continue;
                    }
                    $shipmentItem = $this->convertOrder->itemToShipmentItem($item)->setQty($item->getQtyToShip());
                    $shipment->addItem($shipmentItem);
                }

                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);

                // Add tracking
                $trackData = [
                    'carrier_code' => 'dtdc',
                    'title' => $courier_name,
                    'number' => $referenceNumber,
                ];
                $track = $this->trackFactory->create()->addData($trackData);
                $shipment->addTrack($track);

                // Save shipment and order
                $shipment->save();
                $order->save();

                // Notify customer (optional, existing behavior)
                try {
                    $this->shipmentNotifier->notify($shipment);
                } catch (\Exception $e) {
                    // Notification failure should not mark whole process as failed
                    $this->logger->warning("Shipment notify failed for order {$order->getIncrementId()}: " . $e->getMessage());
                }

                $success[] = sprintf('#%s: Synced & shipped (Ref: %s)', $order->getIncrementId(), $referenceNumber);
                $this->logger->info("Order {$order->getIncrementId()} synced and shipped successfully. Ref: {$referenceNumber}");

            } catch (\Exception $e) {
                $this->logger->error('Shipsy Sync Exception for order ' . $orderId . ' : ' . $e->getMessage());
                $failed[] = sprintf('#%s: %s', $orderId, $e->getMessage());
                // continue to next order
            }
        } // foreach selected

        // Prepare user messages (concise)
        if (!empty($success)) {
            $this->messageManager->addSuccessMessage(__('Processed: %1', implode('; ', $success)));
        }

        if (!empty($failed)) {
            // Use error for failures, but keep message length reasonable
            $this->messageManager->addErrorMessage(__('Failed: %1', implode('; ', $failed)));
        }

        return $resultRedirect->setPath('sales/order/index');
    }

    /**
     * Send shipment request to Shipsy API
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function sendShipmentRequest($data)
    {
        $organisation_id = $this->scopeConfig->getValue(
            'configuration/services/organisation_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $base_url = $this->dataHelper->getBaseUrl($this->scopeConfig, $organisation_id);

        $headers = [
            'Content-Type:application/json',
            'organisation-id:' . $organisation_id,
            'shop-origin:magento',
            'shop-url:' . $this->urlInterface->getBaseUrl(),
            'customer-id:' . $this->cookieManager->getCookie('customer-id'),
            'access-token:' . $this->cookieManager->getCookie('access-token-shipsy'),
        ];

        $jsonPayload = json_encode($data);
        $apiUrl = rtrim($base_url, '/') . '/api/ecommerce/softdata';

        $this->logger->debug('📦 Shipsy API URL: ' . $apiUrl);
        $this->logger->debug('📦 Payload: ' . $jsonPayload);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logger->error('CURL Error on Shipsy API: ' . $curlError);
            throw new \Exception('CURL Error: ' . $curlError);
        }

        $this->logger->debug('📦 Shipsy Response: ' . $result);

        $decoded = json_decode($result, true);
        if ($decoded === null) {
            throw new \Exception('Invalid JSON response from Shipsy API');
        }

        return $decoded;
    }

    /**
     * Get default active service code (Shipsy service model)
     *
     * @return string|null
     */
    protected function getDefaultServiceCode()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $serviceCollection = $objectManager
                ->create(\Shipsy\EcommerceExtension\Model\ResourceModel\Service\Collection::class)
                ->addFieldToFilter('is_default', 1)
                ->addFieldToFilter('active', 1)
                ->getFirstItem();

            return $serviceCollection->getServiceCode();
        } catch (\Exception $e) {
            $this->logger->warning('Could not find default Shipsy service code: ' . $e->getMessage());
            return null;
        }
    }
}
