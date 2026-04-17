<?php
namespace Shipsy\EcommerceExtension\Cron;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class SyncOrder extends AbstractHelper
{
    protected $resourceConnection;
    protected $dataHelper;
    protected $scopeConfig;
    protected $countryFactory;
    protected $logger;
    protected $urlInterface;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
        $this->logger = $logger;
        $this->urlInterface = $urlInterface;
        $this->orderRepository = $orderRepository;
    }

    public function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }



    public function execute()
    {
        try {
            $this->logger->debug('POINT 0');
            $statusToSync = $this->dataHelper->getConfig('store', 'shipsy_optional_settings/optional_settings/shipsy_select_status_to_sync');
            $serviceTypeToUse = $this->dataHelper->getConfig('store', 'shipsy_optional_settings/optional_settings/shipsy_select_service_type');
            $enableAutoSync = $this->dataHelper->getConfig('store', 'shipsy_optional_settings/optional_settings/shipsy_enable_auto_sync');
            $syncOrderLimit = $this->dataHelper->getConfig('store', 'shipsy_optional_settings/optional_settings/shipsy_sync_order_limit') ?? 5;

            if ($enableAutoSync == '0') {
                $this->logger->debug('not running autosync');
                return;
            }

            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('sales_order');
            $this->logger->debug('POINT 1');
            $this->logger->log(100, json_encode($table));
            $unsyncedOrders = $connection->fetchAll("SELECT shipsy_reference_numbers, increment_id, entity_id FROM `" . $table . "` WHERE status IN ( '$statusToSync') AND shipsy_reference_numbers IS NULL ORDER BY created_at DESC LIMIT $syncOrderLimit");
            $this->logger->debug('Unsynced Orders');
            $this->logger->log(100, json_encode($unsyncedOrders));

            foreach ($unsyncedOrders as $unsyncedOrder) {

                $orderId = $unsyncedOrder['entity_id'];
                $order = $this->orderRepository->get($orderId);
                $shippingAddress = $order->getShippingAddress()->getData();
                $addressArray = $this->dataHelper->getAddresses();

                $this->logger->log(100, json_encode($addressArray));

                if (array_key_exists('data', $addressArray) && !empty($addressArray['data'])) {
                    $allAddresses = $addressArray['data'];
                    $forwardAddress = $allAddresses['forwardAddress'];
                    $reverseAddress = $allAddresses['reverseAddress'];
                    $exceptionalReturnAddress = $allAddresses['exceptionalReturnAddress'];
                } else {
                    throw new \Exception('Failed to load addresses');
                }
                $this->logger->debug("start data aarray");
                $this->logger->debug('POINT 2');
                $dataToSendArray = [
                    'consignments' => [
                        [
                            'customer_code' => $this->scopeConfig->getValue('shipsy_customer_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                            'consignment_type' => 'forward',
                            'service_type_id' => $serviceTypeToUse,
                            'reference_number' => '',
                            'load_type' => 'NON-DOCUMENT',
                            'customer_reference_number' => $unsyncedOrder['increment_id'],
                            'commodity_name' => 'Other',
                            'num_pieces' => '1',
                            'origin_details' => [
                                'name' => $forwardAddress['name'],
                                'phone' => $forwardAddress['phone'],
                                'alternate_phone' => $forwardAddress['alternate_phone'],
                                'address_line_1' => $forwardAddress['address_line_1'],
                                'address_line_2' => $forwardAddress['address_line_2'],
                                'pincode' => $forwardAddress['pincode'],
                                'city' => $forwardAddress['city'],
                                'state' => $forwardAddress['state'],
                                'country' => $forwardAddress['country'],
                            ],
                            'destination_details' => [
                                'name' => $shippingAddress['firstname'] . ' ' . $shippingAddress['lastname'],
                                'phone' => $shippingAddress['telephone'],
                                'alternate_phone' => '',
                                'address_line_1' => $shippingAddress['street'],
                                'address_line_2' => '',
                                'pincode' => $shippingAddress['postcode'],
                                'city' => $shippingAddress['city'],
                                'state' => $shippingAddress['region'],
                                'country' => $this->getCountryName($shippingAddress['country_id']),
                            ],
                            'same_pieces' => true,
                            'cod_favor_of' => '',
                            'pieces_detail' => [],
                            'cod_collection_mode' => 'cash',
                            'cod_amount' => 0,
                            'return_details' => [
                                'name' => $reverseAddress['name'],
                                'phone' => $reverseAddress['phone'],
                                'alternate_phone' => $reverseAddress['alternate_phone'],
                                'address_line_1' => $reverseAddress['address_line_1'],
                                'address_line_2' => $reverseAddress['address_line_2'],
                                'pincode' => $reverseAddress['pincode'],
                                'city' => $reverseAddress['city'],
                                'state' => $reverseAddress['state'],
                                'country' => $reverseAddress['country'],
                            ],
                            'exceptional_return_details' => [
                                'name' => $exceptionalReturnAddress['name'],
                                'phone' => $exceptionalReturnAddress['phone'],
                                'alternate_phone' => $exceptionalReturnAddress['alternate_phone'],
                                'address_line_1' => $exceptionalReturnAddress['address_line_1'],
                                'address_line_2' => $exceptionalReturnAddress['address_line_2'],
                                'pincode' => $exceptionalReturnAddress['pincode'],
                                'city' => $exceptionalReturnAddress['city'],
                                'state' => $exceptionalReturnAddress['state'],
                                'country' => $exceptionalReturnAddress['country'],
                            ],
                        ],
                    ]
                ];

                $this->logger->debug("Print dataTosend array");
                $this->logger->log(100, json_encode($dataToSendArray));
                $orderItems = $order->getAllItems();
                $this->logger->debug("Print orderItems");
                $this->logger->log(100, json_encode($orderItems));
                $description = [];
                $declaredValue = 0;
                foreach ($orderItems as $key => $item) {
                    $description[] = (int) $item['qty_ordered'] . ' ' . $item['name'];
                    $declaredValue += $item['row_total_incl_tax'];
                }

                $temp_pieces_details = [
                    'description' => $description,
                    'declared_value' => (int) $declaredValue,
                    'weight' => 1,
                    'height' => 1,
                    'length' => 1,
                    'width' => 1
                ];
                array_push($dataToSendArray['consignments'][0]['pieces_detail'], $temp_pieces_details);
                $this->logger->debug("Print pieces details");
                $this->logger->log(100, json_encode($temp_pieces_details));


                $salesOrder = $connection->getTableName('sales_order');
                $salesShipmentTrack = $connection->getTableName('sales_shipment_track');
                $customerReferenceNumber = $order->getIncrementId();

                $newsql = "SELECT * FROM " . $salesOrder . " WHERE increment_id = $customerReferenceNumber";


                $newsql = "SELECT * FROM " . $salesOrder . " WHERE increment_id = $customerReferenceNumber";

                $queryResult = $connection->fetchAll($newsql);

                $dataToSendJson = json_encode($dataToSendArray);
                $base_url = $this->scopeConfig->getValue('configuration/services/shipsy_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customer_id = $this->scopeConfig->getValue('shipsy_customer_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $access_token = $this->scopeConfig->getValue('shipsy_access_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                
                $headers = [
                    'Content-Type:application/json',
                    'organisation-id:' . $organisation_id,
                    'shop-origin:magento',
                    'shop-url:' . $this->urlInterface->getBaseUrl(),
                    'customer-id:' . $customer_id,
                    'access-token:' . $access_token,
                ];

                $ch = curl_init($base_url . '/api/ecommerce/softdata');
                curl_setopt($ch, CURLOPT_POST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSendJson);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);

                $this->logger->debug("AutoSync Request To Shipsy System");
                $this->logger->log(100, json_encode($dataToSendArray));
                $this->logger->log(100, json_encode($headers));

                curl_close($ch);
                $this->logger->debug('POINT 3');
                $resultdata = json_decode($result, true);

                $this->logger->debug("Autosync Result");
                $this->logger->log(100, json_encode($resultdata));


                if (array_key_exists('data', $resultdata) && array_key_exists('reference_number', $resultdata['data'][0]) && strlen($resultdata['data'][0]['reference_number']) > 0) {
                    $referenceNumber = $resultdata["data"][0]["reference_number"];
                    $storedReferenceNumbers = $queryResult[0]['shipsy_reference_numbers'];

                    $sqlq = "Update " . $salesOrder .
                        " Set `shipsy_reference_numbers` = '$referenceNumber' , `shipsy_cron_error_log` = 'Auto Synced' Where `entity_id` ='$orderId'";
                    $uquery = $connection->query($sqlq);

                    $sqlq2 = "Update " . $salesShipmentTrack .
                        " Set `track_number` = '$referenceNumber' Where `order_id` ='$orderId' ";
                    $uquery2 = $connection->query($sqlq2);
                } else {
                    if (array_key_exists('data', $resultdata) && array_key_exists('message', $resultdata['data'][0])) {
                        $syncErrorMessage = $resultdata['data'][0]['message'];
                        $syncErrorReason = $resultdata["data"][0]["reason"];
                    } elseif (array_key_exists('error', $resultdata)) {
                        $syncErrorMessage = $resultdata['error']['message'];
                        $syncErrorReason = $resultdata["error"]["reason"];
                    }

                    $syncErrorString = "Sync Error Message: " . $syncErrorMessage . " Sync Error Reason:" . $syncErrorReason;

                    $queryToStoreErrorMessage = "Update " . $salesOrder .
                        " Set `shipsy_cron_error_log` = '$syncErrorString' Where `entity_id` ='$orderId'";

                    $uquery = $connection->query($queryToStoreErrorMessage);
                }
            }
            $this->logger->debug('Sync Order Cron Ended');

        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }

    }
}