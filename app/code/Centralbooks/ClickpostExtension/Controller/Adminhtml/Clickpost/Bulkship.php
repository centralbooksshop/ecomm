<?php 
namespace Centralbooks\ClickpostExtension\Controller\Adminhtml\Clickpost;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class MassDelete
 */
class Bulkship extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
	 * @var Curl
     */
    protected $orderManagement;
    protected $collectionFactory;
    protected $resultRedirectFactory;
    protected $order;
	protected $curl;
    protected $date;
	protected $trackFactory;

	/**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $shipmentNotifier;
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
	  * @param \Magento\Sales\Model\Convert\Order $convertOrder
	 * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
	 * @param \Magento\Shipping\Model\ShipmentNotifier    $shipmentNotifier
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeconfig,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Sales\Model\Convert\Order $convertOrder,
        OrderManagementInterface $orderManagement,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Sales\Api\Data\OrderInterface $order,
		\Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
    ) {
        parent::__construct($context, $filter);
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->_scopeConfig = $scopeconfig;
		$this->_orderRepository = $orderRepository;
		$this->_convertOrder = $convertOrder;
		$this->curl = $curl;
		$this->order = $order;
		$this->trackFactory = $trackFactory;
		$this->shipmentNotifier = $shipmentNotifier;
		$this->_messageManager = $messageManager;
		$this->date =  $date;
      }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        $countShipments = 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        
        //$model = $this->_objectManager->create('Magento\Sales\Model\Order');
        foreach ($collection->getItems() as $order)  {  
            if (!$order->getEntityId() || $order->hasShipments() || !$order->canShip()) {
                continue;
            }
			//echo '<pre>';print_r($order->getIncrementId());die;
            $entity_id = $order->getEntityId();
			$increment_id = $order->getIncrementId();
		    $RecommendationApi = $this->_submitRecommendationApi($entity_id,$increment_id);
            if($RecommendationApi){
		       $validRecommendationApi = $RecommendationApi['meta']['message'];
				   if(!empty($validRecommendationApi) && $validRecommendationApi == 'SUCCESS') {
		              $resultRecommendationApi = $RecommendationApi['result']['0']['preference_array']['0'];
					   $cp_id = $resultRecommendationApi['cp_id'];
					   $account_code = $resultRecommendationApi['account_code'];
					   $account_id = $resultRecommendationApi['account_id'];
		                //echo '<pre>';print_r($resultRecommendationApi);die;
				   }
			}

              $shipmentcreateapi = $this->_submitShipmentcreateApi($cp_id,$account_code,$entity_id);
			  //if($shipmentcreateapi)
	if (array_key_exists('result', $shipmentcreateapi) && array_key_exists('order_id', $shipmentcreateapi['result']) && strlen($shipmentcreateapi['result']['order_id']) > 0) 
			  {
               //echo '<pre>';print_r($shipmentcreateapi);die;
			    $referenceNumber = $shipmentcreateapi["result"]["waybill"];
				$shipsy_tracking_url = $shipmentcreateapi["result"]["label"];
				$customerReferenceNumber = $shipmentcreateapi["result"]["order_id"];

				$courier_name = $shipmentcreateapi["result"]["courier_name"];
				$courier_partner_id = $shipmentcreateapi["result"]["courier_partner_id"];

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
				$connection = $resource->getConnection();
				$salesOrder = $resource->getTableName('sales_order');
				$salesOrdersql = "SELECT * FROM " . $salesOrder . " WHERE increment_id = "."'$customerReferenceNumber'";
				$queryResult = $connection->fetchAll($salesOrdersql);
                $storedReferenceNumbers = $queryResult[0]['shipsy_reference_numbers'];
				if (!empty($storedReferenceNumbers)) {
                     //throw new \Exception('Order already synced. Reference Number - ' . $storedReferenceNumbers);
					 $this->_messageManager->addErrorMessage('Order already synced. Reference Number - '. $storedReferenceNumbers);
					 $resultRedirect = $this->resultRedirectFactory->create();
					 return $resultRedirect->setRefererOrBaseUrl();
                }
				$salesOrdersqlq = "UPDATE ".$salesOrder." SET shipsy_reference_numbers='".$referenceNumber."',clickpost_courier_name ='".$courier_name."',shipsy_tracking_url='".$shipsy_tracking_url."' WHERE increment_id="."'$customerReferenceNumber'";
				$uquery = $connection->query($salesOrdersqlq);
				$order = $this->_orderRepository->get($entity_id);
				$shippingAddress = $order->getShippingAddress()->getData();
			   //echo '<pre>';print_r($shippingAddress);die;
			    $origin_state = $shippingAddress['region'];
				// to check order can ship or not
				if (!$order->canShip()) {
					 //throw new \Magento\Framework\Exception\LocalizedException(__('You cant create the Shipment of this order.') );
					 $this->_messageManager->addErrorMessage('You cant create the Shipment of this order.');
					 $resultRedirect = $this->resultRedirectFactory->create();
					 return $resultRedirect->setRefererOrBaseUrl();
				}

				$orderShipment = $this->_convertOrder->toShipment($order);

				foreach ($order->getAllItems() AS $orderItem) {

				 // Check virtual item and item Quantity
				 if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
					continue;
				 }

				 $qty = $orderItem->getQtyToShip();
				 $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qty);

				 $orderShipment->addItem($shipmentItem);
				}

				$orderShipment->register();
				$orderShipment->getOrder()->setIsInProcess(true);
				try {
                    
					 $trackingIds = array(
						'0'=>array('carrier_code'=>'clickpost','title' => $courier_name,'number'=>$referenceNumber)
						);
				 
						/*Add Multiple tracking information*/
						foreach ($trackingIds as $trackingId) {
							$data = array(
								'carrier_code' => $trackingId['carrier_code'],
								'title' => $trackingId['title'],
								'number' => $trackingId['number'],
							);
							$track = $this->trackFactory->create()->addData($data);
							$orderShipment->addTrack($track)->save();
						}
						
					// Save created Order Shipment
					$orderShipment->save();
					$orderShipment->getOrder()->save();
					$shipmentId = $orderShipment->getIncrementId();

					// Send Shipment Email
					$this->shipmentNotifier->notify($orderShipment);
					$orderShipment->save();
				} catch (\Exception $e) {
					//throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
					$this->_messageManager->addErrorMessage($e->getMessage());
					$resultRedirect = $this->resultRedirectFactory->create();
					return $resultRedirect->setRefererOrBaseUrl();
				}
			$clickpost_waybill = $resource->getTableName('clickpost_waybill');
			$clickpostwaybillsql = "SELECT * FROM " . $clickpost_waybill . " WHERE order_increment_id = "."'$customerReferenceNumber'";
			$clickpostqueryResult = $connection->fetchRow($clickpostwaybillsql);
			$destinationname = '';
			if(empty($clickpostqueryResult)) {
				$InsertAwb = $objectManager->create('Centralbooks\ClickpostExtension\Model\Awb');
				$InsertAwb->setWaybill($referenceNumber);
				$InsertAwb->setCourierName($courier_name);
				$InsertAwb->setCourierPartnerId($courier_partner_id);
				$InsertAwb->setState(1);
				$InsertAwb->setStatus("AwbRegistered");
				$InsertAwb->setOrderid($entity_id);
				$InsertAwb->setOrderIncrementId($customerReferenceNumber);
				$InsertAwb->setShipmentTo($destinationname);
				$InsertAwb->setShippingAmount();
				$InsertAwb->setShipmentId($shipmentId);
				$InsertAwb->setShipmentLength();
				$InsertAwb->setShipmentWidth();
				$InsertAwb->setLocation($origin_state);
				$InsertAwb->setShipmentHeight()->save();
				$InsertAwb->save();
			
			} else {
				$awbid = $clickpostqueryResult['awb_id'];
				$updateAwb = $objectManager->create('Centralbooks\ClickpostExtension\Model\Awb')->load($awbid);
				$updateAwb->setWaybill($referenceNumber);
				$updateAwb->setCourierName($courier_name);
				$updateAwb->setCourierPartnerId($courier_partner_id);
				$updateAwb->setState(1);
				$updateAwb->setStatus("AwbRegistered");
				$updateAwb->setOrderid($entity_id);
				$updateAwb->setOrderIncrementId($customerReferenceNumber);
				$updateAwb->setShipmentTo($destinationname);
				$updateAwb->setShippingAmount();
				$updateAwb->setShipmentId($shipmentId);
				$updateAwb->setShipmentLength();
				$updateAwb->setShipmentWidth();
				$updateAwb->setLocation($origin_state);
				$updateAwb->setShipmentHeight()->save();
				$updateAwb->save();
               }

                //$this->_messageManager->addSuccessMessage('Sync successful. Clickpost Reference Number for #'.$customerReferenceNumber. ' - ' . $referenceNumber);
                //$resultRedirect = $this->resultRedirectFactory->create();
				//return $resultRedirect->setRefererOrBaseUrl();
               $countShipments++;
			  } else {
				if (array_key_exists('meta', $shipmentcreateapi) && array_key_exists('message', $shipmentcreateapi['meta'])) {
                    $errorMessage = $shipmentcreateapi['meta']['message'];
                } elseif (array_key_exists('error', $shipmentcreateapi)) {
                    $errorMessage = $shipmentcreateapi['meta']['message'];
                }
                 $this->_messageManager->addErrorMessage('Failed to sync order - '. $errorMessage);
                 $resultRedirect = $this->resultRedirectFactory->create();
                 return $resultRedirect->setRefererOrBaseUrl();
               //throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
              }
			
        }  
		
        $countFailedShipments = $collection->count() - $countShipments;

        if ($countFailedShipments && $countShipments) {
            $this->_messageManager->addErrorMessage(__('%1 order(s) were not shipped through Clickpost.', $countFailedShipments));
        } elseif ($countFailedShipments) {
            $this->_messageManager->addErrorMessage(__('No order(s) were shipped through Clickpost.'));
        }

        if ($countShipments) {
            $this->_messageManager->addSuccessMessage(__('You have shipped through Clickpost %1 order(s).', $countShipments));
        }

        //return $resultRedirect->setPath('sales/order/');
		return $resultRedirect->setRefererOrBaseUrl();
    }

  public function _submitShipmentcreateApi($cp_id,$account_code,$orderid)
	{
   $reseller_name = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_reseller_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   //$reseller_phone = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_reseller_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $channel_name = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_channel_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $clickpost_username = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $clickpost_key = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $clickpost_returnaddress = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_return_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   //$clickpost_latitude = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_latitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   //$clickpost_longitude = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_longitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $clickpost_apiKey = $this->_scopeConfig->getValue('addressautocomplete/general/google_api', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
       try {
            $courier_account_id = $cp_id;
			$courier_account_code = $account_code;
            $order = $this->order->load($orderid); 
			$billingAddress = $order->getBillingAddress()->getData();
            $shippingAddress = $order->getShippingAddress()->getData();
			$postcode = $shippingAddress['postcode'];
            $origin_state = $shippingAddress['region'];

	 if($origin_state == 'Maharashtra')
	 {
      $clickpost_latitude = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_latitude_mh', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $clickpost_longitude = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_longitude_mh', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $reseller_phone = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_reseller_phone_mh', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

	 } else
	 {
      $clickpost_latitude = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_latitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $clickpost_longitude = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_longitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	  $reseller_phone = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_reseller_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
			 $dropaddress = $shippingAddress['street'].','.$shippingAddress['city'].'  '.$shippingAddress['postcode'].','.$shippingAddress['country_id'];
			 $prepAddr = str_replace(' ','+',$dropaddress);

			  //$address = 'BTM 2nd Stage, Bengaluru, Karnataka 560076'; // Address
	         // Get JSON results from this request
			$geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($prepAddr).'&sensor=false&key='.$clickpost_apiKey);
			$geocode = json_decode($geocode, true); // Convert the JSON to an array
             
			 if(isset($geocode['error_message'])){
              $errorMessage = $geocode['error_message'];
			  throw new \Exception($errorMessage);
			 } 
			if(isset($geocode['status']) && ($geocode['status'] == 'OK')) {
			  $drop_lat = $geocode['results'][0]['geometry']['location']['lat']; // Latitude
			  $drop_long = $geocode['results'][0]['geometry']['location']['lng']; // Longitude
			} else {
			  $drop_lat = '';
			  $drop_long = '';
			}

			$grandtotal_amount = $order->getGrandTotal();
			foreach ($order->getInvoiceCollection() as $invoice) {
				$invoiceIncrementID = $invoice->getIncrementId();
				$invoicecreateddate = $invoice->getCreatedAt();
				$invoicedate = $this->date->date($invoicecreateddate)->format('Y-m-d');
			}
			$invoicetime = $this->date->date($invoicecreateddate)->format('H:i:s');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$clickpost_pickup_address = $resource->getTableName('clickpost_pickup_address');


			$addresssql = "SELECT id FROM " . $clickpost_pickup_address . " WHERE forward_state = "."'$origin_state'";
		     $addressres = $connection->fetchRow($addresssql);
		     if(!empty($addressres['id']))
			 {
			   $address_id = $addressres['id'];
             }

		    $pickup_address_sql = "SELECT * FROM " . $clickpost_pickup_address . " WHERE id = "."'$address_id'";
		    $pickup_addressResult = $connection->fetchRow($pickup_address_sql);
            $customerReferenceNumber = $order->getIncrementId();
			$orderItems = $order->getAllItems();
			//echo '<pre>';print_r($shippingAddress);die;
				foreach ($orderItems as $item)
				  {
					   $product_type = $item->getProductType();
					   if($product_type == 'bundle'){
					   $itemsku = $item->getSku();
					   //$itemname = $item->getName();
					   $itemname = 'Book Set';
					   $itemsqty = $item->getQtyOrdered();
					   $itemprice = $item->getPrice();
					   $itemsweight = $item->getWeight();
					   }
				  }
			
			$pickup_timeISO = $invoicedate.'T'.$invoicetime.'+0530';
			$drop_name = $shippingAddress['firstname'].' '.$shippingAddress['lastname'];
                       $dataToSendArray = [
			                'pickup_info' =>  [
                            'pickup_name' => $pickup_addressResult['forward_name'],
				            'pickup_email' => $pickup_addressResult['forward_email'],
                            'pickup_phone' => $pickup_addressResult['forward_phone'],
                            'alternate_phone' => (!empty($pickup_addressResult['forward_alt_phone'])) ? $pickup_addressResult['forward_alt_phone'] : $pickup_addressResult['forward_phone'],
                            'pickup_address' => $pickup_addressResult['forward_line_1'],
				            //'pickup_time' => '2023-01-20T12:00:00Z',
							'pickup_time' => $pickup_timeISO,
                            'pickup_pincode' => $pickup_addressResult['forward_pincode'],
                            'pickup_city' => $pickup_addressResult['forward_city'],
				            'tin' => $pickup_addressResult['forward_tin'],
				            'pickup_state' => $pickup_addressResult['forward_state'],
                            'pickup_country' => $pickup_addressResult['forward_country'],
							'pickup_lat' => "$clickpost_latitude",
                            'pickup_long' => "$clickpost_longitude"
				            
                        ],
                        'drop_info' =>  [
                            'drop_name' => $drop_name,
				            'drop_email' => $shippingAddress['email'], 
                            'drop_phone' => $shippingAddress['telephone'],
                            'alternate_phone' => (!empty($shippingAddress['alternate_number'])) ? $shippingAddress['alternate_number'] : $shippingAddress['telephone'],
                            'drop_address' => $shippingAddress['street'],
                            'drop_pincode' => $shippingAddress['postcode'],
                            'drop_city' => $shippingAddress['city'],
                            'drop_state' => $shippingAddress['region'],
                            'drop_country' => $shippingAddress['country_id'],
							'drop_lat' => "$drop_lat",
                            'drop_long' => "$drop_long"
                        ],
                         'shipment_details' =>  [
                            'height' => '12',
				            'weight' => $itemsweight* '1000', 
                            'length' => '10',
                            'breadth' => '10',
                            'order_type' => 'PREPAID',
                            'invoice_value' => round($grandtotal_amount),
                            'invoice_number' => $invoiceIncrementID,
                            'invoice_date' => $invoicedate,
                            'reference_number' => $customerReferenceNumber,
                            'cod_value' => '0',
							'courier_partner' => $courier_account_id,
							'items' => [[
								'product_url' => '',
								'price' => round($grandtotal_amount),
								'weight' => $itemsweight* '1000',
								'description' => $itemname,
								'quantity' => round($itemsqty),
								'sku' => $itemsku,
								'additional' =>[],
							   ]]

                          ],
						  'additional' =>  [
                            'label' => '1',
				            'return_info' => [
								'name' => $pickup_addressResult['forward_name'],
								'email' => $pickup_addressResult['forward_email'],
								'phone' => $pickup_addressResult['forward_phone'],
								'address' => $pickup_addressResult['forward_line_1'],
								'pincode' => $pickup_addressResult['forward_pincode'],
								'city' => $pickup_addressResult['forward_city'],
								 'tin' => $pickup_addressResult['forward_tin'],
								 'state' => $pickup_addressResult['forward_state'],
								'country' => $pickup_addressResult['forward_country'],
								], 
                            'reseller_info' => [
								'name' => $reseller_name,
								'phone' => $reseller_phone,
								],
                            //'awb_number' => '',
                            'delivery_type' => 'FORWARD',
                            'async' => '',
                            'gst_number' => $pickup_addressResult['forward_tin'],
                            'account_code' => $courier_account_code,
                            'from_wh' => 'From Warehouse',
                            'to_wh' => 'To Warehouse',
							'channel_name' => $channel_name,
							'order_date' => $invoicedate,
                            'enable_whatsapp' => '',
                            'is_fragile' => '1',
                            'is_dangerous' => '1',
                            'order_id' => $customerReferenceNumber,
                        ], 
                   ];

			 //echo '<pre>';print_r($dataToSendArray);
			 $dataToSendJson = json_encode($dataToSendArray);
			//Order Creation V3 API (Forward) start 
			  $ordercreation_api_url = 'https://www.clickpost.in/api/v3/create-order/?username='.$clickpost_username.'&key='.$clickpost_key;
			  $this->curl->setOption(CURLOPT_HEADER, 0);
			  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
			  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
			  //set curl header
			  $this->curl->addHeader("Content-Type", "application/json");
			  //post request with url and data
			  $this->curl->post($ordercreation_api_url, $dataToSendJson);
			  //read response
			  $result = $this->curl->getBody();
			  $resultdata = json_decode($result, true);
			  return $resultdata;
			//Order Creation V3 API (Forward) code end
			
	   } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage('Failed to sync order - '. $e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
       }
	}

	public function _submitRecommendationApi($orderid,$increment_id)
	{
           try {
           $clickpost_username = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           $clickpost_key = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           $clickpost_returnaddress = $this->_scopeConfig->getValue('clickpost/clickpostservices/clickpost_return_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $order = $this->order->load($orderid); 
			$billingAddress = $order->getBillingAddress()->getData();
            $shippingAddress = $order->getShippingAddress()->getData();
			$postcode = $shippingAddress['postcode'];
			$region = $shippingAddress['region'];
			$invoicevalue = $order->getGrandTotal();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$clickpost_pickup_address = $resource->getTableName('clickpost_pickup_address');

			$addresssql = "SELECT id FROM " . $clickpost_pickup_address . " WHERE forward_state = "."'$region'";
		     $addressres = $connection->fetchRow($addresssql);
		     if(!empty($addressres['id']))
			 {
			   $address_id = $addressres['id'];
             }
			$pickup_address_sql = "SELECT * FROM " . $clickpost_pickup_address . " WHERE id = "."'$address_id'";
			$pickup_addressResult = $connection->fetchRow($pickup_address_sql);
			$pickup_pincode = $pickup_addressResult['forward_pincode'];
		    //echo '<pre>';print_r($shippingAddress);die;
			
		    $postarrayData= [[
		     'pickup_pincode'=> $pickup_pincode,
		     'drop_pincode'=> $postcode,
			 'order_type'=>'PREPAID',
		     'reference_number'=>$increment_id,
			 'item'=>'books',
		     'invoice_value'=> round($invoicevalue),
			 'delivery_type'=>'FORWARD',
		     'additional'=> []
			]];

			 $jsonData = json_encode($postarrayData, TRUE);
			 //echo '<pre>';print_r($jsonData);
			 //$jsondecodeData = json_decode($jsonData, TRUE);
			  //echo '<pre>';print_r($jsondecodeData);
              $recommendation_api_url = 'https://www.clickpost.in/api/v1/recommendation_api/?key='.$clickpost_key;
			  //$this->curl->setOption(CURLOPT_USERPWD, $username . ":" . $password);
			  $this->curl->setOption(CURLOPT_HEADER, 0);
			  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
			  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
			  //set curl header
			  $this->curl->addHeader("Content-Type", "application/json");
			  //post request with url and data
			  $this->curl->post($recommendation_api_url, $jsonData);
			  //read response
			  $result = $this->curl->getBody();
			  $response = json_decode($result, true);
			  return $response ;
			  //echo '<pre>';print_r($response);die;
		
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
	}
}