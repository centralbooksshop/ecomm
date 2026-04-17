<?php
namespace Delhivery\Lastmile\Controller\Adminhtml\Delhivery;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Delhivery\Lastmile\Helper\Data;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Shipping\Model\ShipmentNotifier;
use Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory as AwbCollectionFactory;
use Delhivery\Lastmile\Model\ResourceModel\Pincode\CollectionFactory as PincodeCollectionFactory;
use Delhivery\Lastmile\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Psr\Log\LoggerInterface;

class Bulkship extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;
    protected $collectionFactory;
    protected $resultRedirectFactory;
    protected $helper;
    protected $scopeConfig;
    protected $convertOrder;
    protected $shipmentNotifier;
    protected $awbCollectionFactory;
    protected $pincodeCollectionFactory;
    protected $locationCollectionFactory;
    protected $trackFactory;
    protected $logger;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $helper
     * @param OrderManagementInterface $orderManagement
     * @param ConvertOrder $convertOrder
     * @param ShipmentNotifier $shipmentNotifier
     * @param AwbCollectionFactory $awbCollectionFactory
     * @param PincodeCollectionFactory $pincodeCollectionFactory
     * @param LocationCollectionFactory $locationCollectionFactory
     * @param TrackFactory $trackFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        OrderManagementInterface $orderManagement,
        ConvertOrder $convertOrder,
        ShipmentNotifier $shipmentNotifier,
        AwbCollectionFactory $awbCollectionFactory,
        PincodeCollectionFactory $pincodeCollectionFactory,
        LocationCollectionFactory $locationCollectionFactory,
        TrackFactory $trackFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $filter);
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->convertOrder = $convertOrder;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->awbCollectionFactory = $awbCollectionFactory;
        $this->pincodeCollectionFactory = $pincodeCollectionFactory;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->trackFactory = $trackFactory;
        $this->logger = $logger;
    }

    /**
     * Process bulk shipment for selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countShipments = 0;
        $countFailedShipments = 0;
        $resultRedirect = $this->resultRedirectFactory->create();

        // Validate API configuration
        if (!$this->validateApiConfig()) {
            $this->messageManager->addErrorMessage(__('Delhivery API configuration is incomplete.'));
            return $resultRedirect->setPath('sales/order/');
        }

        /*$locationModel = $this->getPickupLocation();
        if (!$locationModel) {
            $this->messageManager->addErrorMessage(__('No pickup location configured.'));
            return $resultRedirect->setPath('sales/order/');
        }*/

        foreach ($collection->getItems() as $order) {
            try {

				$schoolId = (int)$order->getData('school_id');
				$pickupRegion = 1; // default Telangana

				if ($schoolId > 0) {
					$resource = \Magento\Framework\App\ObjectManager::getInstance()
						->get(\Magento\Framework\App\ResourceConnection::class);

					$connection  = $resource->getConnection();
					$schoolTable = $resource->getTableName('schools_registered');

					$pickupRegionDb = $connection->fetchOne(
						"SELECT delhivery_pickup_region FROM {$schoolTable} WHERE school_name = ? LIMIT 1",
						[$schoolId]
					);

					if ($pickupRegionDb !== false && $pickupRegionDb !== null) {
						$pickupRegion = (int)$pickupRegionDb;
					}
				}

				$locationModel = $this->getPickupLocation($pickupRegion);

				if (!$locationModel || !$locationModel->getId()) {
					$this->messageManager->addErrorMessage(__('No pickup location configured.'));
					return $resultRedirect->setPath('sales/order/');
				}


                if ($this->processOrderShipment($order, $locationModel ,$pickupRegion)) {
                    $countShipments++;
                } else {
                    $countFailedShipments++;
                }
            } catch (\Exception $e) {
                $this->logger->error("Delhivery Bulk Ship Error for Order {$order->getIncrementId()}: " . $e->getMessage());
                $countFailedShipments++;
            }
        }

        $this->showResultMessages($countShipments, $countFailedShipments);
        return $resultRedirect->setPath('sales/order/');
    }

    /**
     * Validate API configuration
     */
    protected function validateApiConfig()
    {
        $clientId = $this->getScopeConfig('delhivery_lastmile/general/client_id');
        $token = $this->getScopeConfig('delhivery_lastmile/general/license_key');
        $apiUrl = $this->helper->getApiUrl('manifestAWB');

        return !empty($clientId) && !empty($token) && !empty($apiUrl);
    }

    /**
     * Get pickup location
     */
    protected function getPickupLocationdup()
    {
        $locationCollection = $this->locationCollectionFactory->create();
        return $locationCollection->getFirstItem();
    }

	protected function getPickupLocation($pickupRegion = 1)
	{
		$collection = $this->locationCollectionFactory->create();

		if ($pickupRegion == 1) {
			// Telangana
			$collection->addFieldToFilter('name', 'CBS HUB PRIVATE LIMITED');
		} elseif ($pickupRegion == 2) {
			$collection->addFieldToFilter('name', 'CBS Hub Pvt Ltd Mumbai');
		}

		return $collection->getFirstItem();
	}

    /**
     * Process individual order shipment
     */
    protected function processOrderShipment($order, $locationModel , $pickupRegion)
    {
        // Validate order status
        if (!$this->isOrderEligibleForShipment($order)) {
            return false;
        }

        // Check pincode serviceability
        $address = $order->getShippingAddress();
        if (!$this->isPincodeServiceable($address->getPostcode())) {
            $this->logger->info("Order {$order->getIncrementId()}: Pincode {$address->getPostcode()} not serviceable");
            return false;
        }

        // Get available AWB
        $awbNumber = $this->getAvailableAwb();
        if (!$awbNumber) {
            $this->messageManager->addErrorMessage(__('All AWB numbers have been used. Download fresh AWB first.'));
            return false;
        }

        // Create Delhivery shipment
        $shipmentData = $this->prepareShipmentData($order, $awbNumber, $locationModel, $pickupRegion);
        $apiResponse = $this->createDelhiveryShipment($shipmentData);

        if ($apiResponse && $this->isApiResponseSuccessful($apiResponse)) {

            // Update AWB status
            $this->updateAwbStatus($awbNumber, $order->getEntityId(), $locationModel);
            
            // Create Magento shipment
            $this->createMagentoShipment($order, $awbNumber);
            
            return true;
        } else {
            $this->logger->error("Delhivery API failed for Order {$order->getIncrementId()}: " . json_encode($apiResponse));
            return false;
        }
    }

    /**
     * Check if order is eligible for shipment
     */
    protected function isOrderEligibleForShipment($order)
    {
        return $order->getStatus() === 'assigned_to_picker' && 
               $order->getEntityId() && 
               !$order->hasShipments() && 
               $order->canShip();
    }

    /**
     * Check pincode serviceability
     */
    protected function isPincodeServiceable($pincode)
    {
        $pincodeCollection = $this->pincodeCollectionFactory->create()
            ->addFieldToFilter('pin', $pincode);
        
        return $pincodeCollection->getSize() > 0;
    }

    /**
     * Get available AWB number
     */
    protected function getAvailableAwb()
    {
        $awbCollection = $this->awbCollectionFactory->create()
            ->addFieldToFilter('state', 2)
            ->setPageSize(1);

        return $awbCollection->getSize() > 0 ? $awbCollection->getFirstItem()->getAwb() : null;
    }

    /**
     * Prepare shipment data for Delhivery API
     */
    protected function prepareShipmentData($order, $awbNumber, $locationModel, $pickupRegion)
    {
        $address = $order->getShippingAddress();
        $items = $this->getOrderItemsData($order);
        
        $methodCode = $this->isCodOrder($order) ? "COD" : "Pre-Paid";
        $codAmount = $this->isCodOrder($order) ? $order->getGrandTotal() : "0.00";

        $shipmentData = [
            'client' => $this->getScopeConfig('delhivery_lastmile/general/client_id'),
            'name' => $address->getName(),
            'order' => $order->getIncrementId(),
            'products_desc' => $items['name'],
            'order_date' => $order->getUpdatedAt(),
            'payment_mode' => $methodCode,
            'total_amount' => $order->getGrandTotal(),
            'cod_amount' => $codAmount,
            'add' => $address->getStreet(),
            'city' => $address->getCity(),
            'state' => $address->getRegion(),
            'waybill' => $awbNumber,
            'country' => $address->getCountryId(),
            'phone' => $address->getTelephone(),
            'pin' => $address->getPostcode(),
			'return_add' => $this->getReturnAddress($pickupRegion),
            'quantity' => $items['qty'],
			'weight' => $items['weight'],
            'consignee_tin' => $this->getScopeConfig('delhivery_lastmile/general/consignee_tin'),
            'commodity_value' => $order->getGrandTotal(),
            'tax_value' => $order->getTaxAmount(),
            'sales_tax_form_ack_no' => $this->getScopeConfig('delhivery_lastmile/general/sale_tax_form'),
            'shipment_length' => '',
            'shipment_width' => '',
            'shipment_height' => ''
        ];

        $pickupLocation = [
            'add' => $locationModel->getAddress(),
            'city' => $locationModel->getCity(),
            'country' => 'India',
            'name' => $locationModel->getName(),
            'phone' => $locationModel->getPhone(),
            'pin' => $locationModel->getPin()
        ];

        return [
            'pickup_location' => $pickupLocation,
            'shipments' => [$shipmentData]
        ];
    }

	protected function getReturnAddress($pickupRegion = 1)
	{
		if ($pickupRegion == 1) {
			// Telangana
			return $this->getScopeConfig('delhivery_lastmile/general/return_address');
		} elseif ($pickupRegion == 2) {
			// Mumbai
			return $this->getScopeConfig('delhivery_lastmile/general/return_address_mumbai');
		}

		// fallback
		return $this->getScopeConfig('delhivery_lastmile/general/return_address');
	}

    /**
     * Get order items data
     */
    protected function getOrderItemsData($order)
    {
        $itemsData = [
            'qty' => 0,
            'name' => '',
            'weight' => 0,
            'products' => []
        ];

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyToShip = $orderItem->getQtyToShip();
            $itemsData['qty'] += $qtyToShip;
            $itemsData['name'] .= $orderItem->getName() . ', ';
			$weightKg = $orderItem->getWeight() ?: 0.1; // 100g fallback
            $itemsData['weight'] += ($weightKg * $qtyToShip) * 1000;
            //$itemsData['weight'] += ((float)$orderItem->getWeight() * (int)$qtyToShip) * 1000;
            $itemsData['products'][] = $orderItem->getProductId();
        }

        $itemsData['name'] = rtrim($itemsData['name'], ', ');
        return $itemsData;
    }

    /**
     * Check if order is COD
     */
    protected function isCodOrder($order)
    {
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $codMethods = ['cashondelivery', $this->getScopeConfig('delhivery_lastmile/general/cod_method')];
        
        return in_array($paymentMethod, $codMethods);
    }

    /**
     * Create shipment via Delhivery API
     */
    protected function createDelhiveryShipment($shipmentData)
    {
        $token = trim($this->getScopeConfig('delhivery_lastmile/general/license_key'));
        $url = $this->helper->getApiUrl('manifestAWB') . "cmu/push/json/?token=" . $token;

        $params = [
            'format' => 'json',
            'data' => json_encode($shipmentData)
        ];

        return $this->helper->Executecurl($url, 'post', $params);
    }

    /**
     * Check if API response is successful
     */
    protected function isApiResponseSuccessful($apiResponse)
    {
        $response = json_decode($apiResponse, true);
        return isset($response['packages'][0]['status']) && 
               $response['packages'][0]['status'] == 'Success';
    }

    /**
     * Update AWB status in database
     */
    protected function updateAwbStatus($awbNumber, $orderId, $locationModel)
    {
        $awbCollection = $this->awbCollectionFactory->create()
            ->addFieldToFilter('awb', $awbNumber);

        if ($awbCollection->getSize() > 0) {
            foreach ($awbCollection as $awbModel) {
                $awbModel->setPickupLocationId($locationModel->getLocationId());
                $awbModel->setReturnAddress($this->getScopeConfig('delhivery_lastmile/general/return_address'));
                $awbModel->setStatusType('UD');
                $awbModel->setState(1);
                $awbModel->setOrderId($orderId);
                $awbModel->setData('status', 'Manifested');
                $awbModel->setUpdatedAt(date('Y-m-d H:i:s'));
                $awbModel->save();
            }
        }
    }

    /**
     * Create Magento shipment
     */
    protected function createMagentoShipment($order, $awbNumber)
    {
        $order->setData('cbo_courier_name', 'Delhivery');
		$order->setData('cbo_reference_number', $awbNumber);
		
		
		$shipment = $this->convertOrder->toShipment($order);

        // Add items to shipment
        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)
                ->setQty($orderItem->getQtyToShip());
            $shipment->addItem($shipmentItem);
        }

        // Register shipment
        $shipment->register();

        // Add tracking information
        $trackData = [
            'carrier_code' => 'delhivery',
            'title' => 'Delhivery',
            'number' => $awbNumber,
        ];

        $track = $this->trackFactory->create()->addData($trackData);
        $shipment->addTrack($track);

        // Save shipment and order
        $shipment->getOrder()->setIsInProcess(true);
        
        $shipment->save();
        $shipment->getOrder()->save();

        // Send shipment email
        $this->shipmentNotifier->notify($shipment);

        return true;
    }

    /**
     * Show result messages
     */
    protected function showResultMessages($successCount, $failedCount)
    {
        if ($failedCount > 0 && $successCount > 0) {
            $this->messageManager->addErrorMessage(
                __('%1 order(s) were not shipped through Delhivery.', $failedCount)
            );
        } elseif ($failedCount > 0) {
            $this->messageManager->addErrorMessage(
                __('No order(s) were shipped through Delhivery.')
            );
        }

        if ($successCount > 0) {
            $this->messageManager->addSuccessMessage(
                __('You have shipped through Delhivery %1 order(s).', $successCount)
            );
        }
    }

    /**
     * Get scope config value
     */
    public function getScopeConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}