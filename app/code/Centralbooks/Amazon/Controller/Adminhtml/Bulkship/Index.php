<?php
namespace Centralbooks\Amazon\Controller\Adminhtml\Bulkship;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Centralbooks\Amazon\Helper\Auth;

class Index extends Action
{
    protected $messageManager;
    protected $urlInterface;
    protected $scopeConfig;
    protected $orderRepository;
    protected $convertOrder;
    protected $trackFactory;
    protected $shipmentNotifier;
    protected $logger;
    protected $directoryList;
    protected $ioFile;
    protected $authHelper;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        UrlInterface $urlInterface,
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        ConvertOrder $convertOrder,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier,
        LoggerInterface $logger,
        DirectoryList $directoryList,
        File $ioFile,
        Auth $authHelper
    ) {
        parent::__construct($context);

        $this->messageManager   = $messageManager;
        $this->urlInterface     = $urlInterface;
        $this->scopeConfig      = $scopeConfig;
        $this->orderRepository  = $orderRepository;
        $this->convertOrder     = $convertOrder;
        $this->trackFactory     = $trackFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->logger           = $logger;
        $this->directoryList    = $directoryList;
        $this->ioFile           = $ioFile;
        $this->authHelper       = $authHelper;
    }

    /**
     * Normalize an address line for Amazon:
     * - decode & strip HTML entities/tags
     * - remove control characters (newlines, tabs)
     * - collapse whitespace to single spaces
     * - trim and limit length
     *
     * @param string|null $value
     * @param int $maxLen
     * @return string
     */
    protected function normalizeAddressLine($value, $maxLen = 100)
    {
        if ($value === null) return '';

        $value = (string)$value;

        // decode HTML entities and strip tags
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);

        // remove all control characters (including newlines, tabs) -> replace with single space
        // \p{C} matches invisible, control characters
        $value = preg_replace('/\p{C}+/u', ' ', $value);

        // collapse any whitespace (space, newline, tab) to a single space
        $value = preg_replace('/\s+/u', ' ', $value);

        $value = trim($value);

        if (mb_strlen($value) > $maxLen) {
            $value = mb_substr($value, 0, $maxLen);
        }

        return $value;
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        try {
            $selected = $this->getRequest()->getParam("selected");

            if (empty($selected)) {
                throw new \Exception("No orders selected.");
            }

            // Get Amazon Access Token
            $accessToken = $this->authHelper->getAccessToken();
            if (!$accessToken) {
                throw new \Exception("Unable to generate Amazon Access Token.");
            }

            $success = 0;
            $failures = [];

            foreach ($selected as $orderId) {
                try {
                    $order = $this->orderRepository->get((int)$orderId);

                    if (!$order->getId()) {
                        $failures[] = "Order ID {$orderId} not found.";
                        continue;
                    }

                    if ($order->getStatus() !== "assigned_to_picker") {
                        $failures[] = "Order {$order->getIncrementId()} has invalid status.";
                        continue;
                    }

					$invoiceCollection = $order->getInvoiceCollection();
					$invoiceIncrementId = null;

					if ($invoiceCollection->getSize()) {
						$invoice = $invoiceCollection->getFirstItem();
						$invoiceIncrementId = $invoice->getIncrementId();
					}

					// Fallback if invoice not created yet
					if (!$invoiceIncrementId) {
						$invoiceIncrementId = $order->getIncrementId();
					}

					$schoolId = (int)$order->getData('school_id');

					// Default pickup region = Telangana
					$pickupRegion = 1;

					if ($schoolId > 0) {

						$resource = \Magento\Framework\App\ObjectManager::getInstance()
							->get(\Magento\Framework\App\ResourceConnection::class);

						$connection  = $resource->getConnection();
						$schoolTable = $resource->getTableName('schools_registered');

						$pickupRegionDb = $connection->fetchOne(
							"SELECT pickup_region FROM {$schoolTable} WHERE school_name = ? LIMIT 1",
							[$schoolId]
						);

						if ($pickupRegionDb !== false && $pickupRegionDb !== null) {
							$pickupRegion = (int)$pickupRegionDb;
						}
					}

					// Final decision
					if ($pickupRegion === 1) {
						 // Dynamic shipFrom (JSON config)
						$shipFromConfig = $this->scopeConfig->getValue(
							"amazon_configuration/general/ship_from_json",
							\Magento\Store\Model\ScopeInterface::SCOPE_STORE
						);

						$returnToConfig = $this->scopeConfig->getValue(
							"amazon_configuration/general/return_to_json",
							\Magento\Store\Model\ScopeInterface::SCOPE_STORE
						);
					} elseif ($pickupRegion === 2) {
						$shipFromConfig = $this->scopeConfig->getValue(
                        "amazon_configuration/general/ship_from_mumbai_json",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
						);

						$returnToConfig = $this->scopeConfig->getValue(
							"amazon_configuration/general/return_to_mumbai_json",
							\Magento\Store\Model\ScopeInterface::SCOPE_STORE
						);
					}

                    // Shipping and Billing
                    $shipping = $order->getShippingAddress()->getData();
                    $billing  = $order->getBillingAddress()->getData();

					/*$origin_state = $shipping['region'] ?? '';

					if ($origin_state === 'Telangana') {
						 
					} elseif ($origin_state === 'Maharashtra') {
						
					}*/

                    // GST ID from config (required for this account)
                    $gstId = trim($this->scopeConfig->getValue(
                        "amazon_configuration/general/gst_id",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ));

                    if (empty($gstId)) {
                        $failures[] = "Order {$order->getIncrementId()}: GST ID (gst_id) not configured for Amazon shipments. Please set it under Stores > Configuration > Amazon Configuration.";
                        $this->logger->error("Amazon OneClickShipment skipped: GST ID not configured.");
                        continue;
                    }

                    $shipFrom = [
                        "name"          => "Lakeeshop",
                        "addressLine1"  => "72/2 ATM Square 1st Floor Anna Salai",
                        "addressLine2"  => "",
                        "addressLine3"  => "",
                        "stateOrRegion" => "Tamil Nadu",
                        "postalCode"    => "600002",
                        "city"          => "Chennai",
                        "countryCode"   => "IN",
                        "email"         => "itprojects@lakeeshop.com",
                        "phoneNumber"   => "8667757668"
                    ];

                    if (!empty($shipFromConfig)) {
                        $decodedShipFrom = json_decode($shipFromConfig, true);
                        if (is_array($decodedShipFrom)) {
                            $shipFrom = $decodedShipFrom;
                        }
                    }

					$returnTo = [
                        "name"          => "Lakeeshop",
                        "addressLine1"  => "72/2 ATM Square 1st Floor Anna Salai",
                        "addressLine2"  => "",
                        "addressLine3"  => "",
                        "stateOrRegion" => "Tamil Nadu",
                        "postalCode"    => "600002",
                        "city"          => "Chennai",
                        "countryCode"   => "IN",
                        "email"         => "itprojects@lakeeshop.com",
                        "phoneNumber"   => "8667757668"
                    ];

                    if (!empty($returnToConfig)) {
                        $decodedReturnTo = json_decode($returnToConfig, true);
                        if (is_array($decodedReturnTo)) {
                            $returnTo = $decodedReturnTo;
                        }
                    }

                    // Build dynamic package based on order items
                    $packageItems = [];
                    $totalWeight = 0;
                    $referenceId = $order->getIncrementId();

					foreach ($order->getAllVisibleItems() as $item) {

						$qty = (int)$item->getQtyOrdered();

						// Convert weight from KG to GRAMS properly
						$itemWeightGrams = (float)$item->getWeight() > 0
							? (int) round((float)$item->getWeight() * 1000)
							: 10;

						$packageItems[] = [
							"itemValue" => [
								"value" => round($item->getPrice(), 2),
								"unit"  => "INR"
							],
							"description" => $item->getName(),
							"itemIdentifier" => uniqid("itm_"),
							"quantity" => $qty,
							"weight" => [
								"unit" => "GRAM",
								"value" => $itemWeightGrams
							],
							"isHazmat" => false,
							"invoiceDetails" => [
								"invoiceNumber" => $order->getIncrementId(),
								"invoiceDate"   => gmdate("Y-m-d\TH:i:s\Z")
							]
						];

						$totalWeight += $itemWeightGrams * $qty;
					}


                    // --- SANITIZE / BUILD shipTo ---
                    $streetRaw = $shipping['street'] ?? '';
                    $streetLines = [];

                    if (is_array($streetRaw)) {
                        foreach ($streetRaw as $line) {
                            $n = $this->normalizeAddressLine($line);
                            if ($n !== '') $streetLines[] = $n;
                        }
                    } else {
                        // split on common delimiters including newline, comma, semicolon
                        $parts = preg_split('/[,\r\n;]+/', (string)$streetRaw);
                        foreach ($parts as $p) {
                            $n = $this->normalizeAddressLine($p);
                            if ($n !== '') $streetLines[] = $n;
                        }
                    }

                    // ensure there's at least one line
                    if (empty($streetLines)) {
                        $fallback = (($shipping['city'] ?? '') . ' ' . ($shipping['postcode'] ?? ''));
                        $streetLines[] = $this->normalizeAddressLine($fallback);
                    }

                    // map to addressLine1/2/3 (Amazon accepts up to 3)
                    $addressLine1 = $streetLines[0] ?? '';
                    $addressLine2 = $streetLines[1] ?? '';
                    $addressLine3 = $streetLines[2] ?? '';

                    $shipTo = [
                        "name"          => $this->normalizeAddressLine(($shipping["firstname"] ?? '') . ' ' . ($shipping["lastname"] ?? ''), 80),
                        "addressLine1"  => $addressLine1,
                        "addressLine2"  => $addressLine2,
                        "addressLine3"  => $addressLine3,
                        "postalCode"    => $this->normalizeAddressLine($shipping["postcode"] ?? '', 20),
                        "city"          => $this->normalizeAddressLine($shipping["city"] ?? '', 60),
                        "stateOrRegion" => $this->normalizeAddressLine($shipping["region"] ?? '', 60),
                        "countryCode"   => $this->normalizeAddressLine($shipping["country_id"] ?? '', 6),
                        "email"         => $this->normalizeAddressLine($billing["email"] ?? '', 100),
                        "phoneNumber"   => $this->normalizeAddressLine($shipping["telephone"] ?? '', 30)
                    ];

                    // Log sanitized shipTo for debugging
                    $this->logger->debug('Sanitized shipTo for order ' . $order->getIncrementId() . ': ' . json_encode($shipTo));

                    // AMAZON ONE CLICK DYNAMIC PAYLOAD
                    $payload = [
						"channelDetails" => [
							"channelType" => "EXTERNAL"
						],

						"labelSpecifications" => [
							"format" => "PNG",
							"dpi" => 300,
							"pageLayout" => "DEFAULT",
							"needFileJoining" => false,
							"requestedDocumentTypes" => ["LABEL"],
							"size" => [
								"length" => 6.0,
								"width"  => 4.0,
								"unit"   => "INCH"
							]
						],

						"shipFrom" => $shipFrom,
						"shipTo"   => $shipTo,
						"returnTo" => $returnTo,

						"packages" => [[
							"dimensions" => [
								"length" => 20,
								"width"  => 14,
								"height" => 10,
								"unit"   => "CENTIMETER"
							],
							"weight" => [
								"unit"  => "GRAM",
								"value" => $totalWeight
							],
							"insuredValue" => [
								"value" => 1,
								"unit"  => "INR"
							],
							"packageClientReferenceId" => $referenceId,
                            "sellerDisplayName" => 'CBS HUB PRIVATE LIMITED',
							"items" => $packageItems
						]],

						"serviceSelection" => [
							"serviceId" => ["SWA-IN-OA"]
						],

						"taxDetails" => [[
							"taxType" => "GST",
							"taxRegistrationNumber" => $gstId
						]],

						 "additionalShipmentDetails" => [
							"orderNumber" => $order->getIncrementId()
						]
					];

                   

                    // Log it
                    $this->logger->debug("Amazon OneClickShipment Payload for order " . $order->getIncrementId() . ": " . json_encode($payload));
                    // echo '<pre>'; print_r($payload); die;

                    // Call the API
                    list($ok, $resp) = $this->callOneClickShipment($payload, $accessToken);

                    if (!$ok) {
                        $failures[] = "Order {$order->getIncrementId()} API ERROR: {$resp}";
                        continue;
                    }

                    $respData = $resp["payload"] ?? $resp;

                    // Extract tracking
                    $trackingId =
                        $respData["trackingId"]
                        ?? ($respData["packageDocumentDetails"][0]["trackingId"] ?? null)
                        ?? ($respData["shipmentId"] ?? null);

                    if (!$trackingId) {
                        $failures[] = "Order {$order->getIncrementId()}: No trackingId returned.";
                        continue;
                    }

                    // Save tracking to order
                    $order->setData("cbo_courier_name", "Amazon");
                    $order->setData("cbo_reference_number", $trackingId);
                    $this->orderRepository->save($order);

                    // Shipment creation
                    if ($order->canShip()) {
                        $shipment = $this->convertOrder->toShipment($order);

                        foreach ($order->getAllItems() as $item) {
                            if (!$item->getQtyToShip() || $item->getIsVirtual()) continue;
                            $shipmentItem = $this->convertOrder->itemToShipmentItem($item)
                                                              ->setQty($item->getQtyToShip());
                            $shipment->addItem($shipmentItem);
                        }

                        $shipment->register();
                        $shipment->getOrder()->setIsInProcess(true);

                        $track = $this->trackFactory->create()->addData([
                            "carrier_code" => "amazon",
                            "title"        => "Amazon",
                            "number"       => $trackingId
                        ]);
                        $shipment->addTrack($track);

                        $shipment->save();
                        $shipment->getOrder()->save();

                        try {
                            $this->shipmentNotifier->notify($shipment);
                        } catch (\Exception $e) {
                            $this->logger->warning("Notify failed: " . $e->getMessage());
                        }
                    }

                    $success++;

                } catch (\Exception $err) {
                    $failures[] = "Order {$orderId}: " . $err->getMessage();
                    $this->logger->error($err->getMessage());
                }
            }

            if ($success > 0) {
                $this->messageManager->addSuccessMessage("Amazon shipments created for {$success} order(s).");
            }
            if (!empty($failures)) {
                $this->messageManager->addErrorMessage(implode("<br>", $failures));
            }

            return $redirect->setPath("sales/order/index");

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            return $redirect->setPath("sales/order/index");
        }
    }

    protected function callOneClickShipment(array $payload, $accessToken)
    {

        $endpoint = $this->scopeConfig->getValue(
                        "amazon_configuration/general/oneclickshipment_api_url",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
        //$endpoint = "https://sandbox.sellingpartnerapi-eu.amazon.com/shipping/v2/oneClickShipment";

        $headers = [
            "Content-Type: application/json",
            "x-amz-access-token: {$accessToken}",
            "x-amzn-shipping-business-id: AmazonShipping_IN"
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($err) return [false, "CURL Error: {$err}"];
        if ($code < 200 || $code >= 300) return [false, "HTTP {$code}: {$response}"];

        $decoded = json_decode($response, true);

        if (!$decoded) return [false, "Invalid JSON response"];

        return [true, $decoded];
    }
}
