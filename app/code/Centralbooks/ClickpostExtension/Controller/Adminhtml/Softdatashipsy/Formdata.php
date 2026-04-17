<?php

namespace Centralbooks\ClickpostExtension\Controller\Adminhtml\Softdatashipsy;

class Formdata extends \Magento\Framework\App\Action\Action
{
    /**
	* @var Curl
	*/
    protected $curl;
	
	/**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_convertOrder;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;
	
	protected $_messageManager;
    protected $urlInterface;
    protected $_cookieManager;
	protected $trackFactory;
	protected $date;

	/**
     * @param Context                                     $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Convert\Order          $convertOrder
     * @param \Magento\Shipping\Model\ShipmentNotifier    $shipmentNotifier
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
		\Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Centralbooks\ClickpostExtension\Helper\Data $dataHelper,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
    ) {
        parent::__construct($context);
        $this->_messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->_cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
		$this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
		$this->trackFactory = $trackFactory;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->dataHelper = $dataHelper;
		$this->curl = $curl;
		$this->date =  $date;
    }

    public function getAddresses()
    {
       
    }
    
    public function execute()
    {
        $requiredFields = ['customer-reference-number', 'account_code', 'courier-type', 'num-pieces',
        'origin-name', 'origin-number', 'origin-line-1', 'origin-pincode', 'origin-state', 'origin-country',
        'destination-name', 'destination-email', 'destination-number', 'destination-line-1', 'destination-pincode', 'destination-state', 'destination-country',
        'cod-collection-mode', 'cod-amount', 'description', 'declared-value', 'weight', 'height', 'length', 'width'
        ];

   try {

    $postRequestParams = $this->getRequest()->getPostValue();
	//echo '<pre>';print_r($postRequestParams);die;
   $reseller_name = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_reseller_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $channel_name = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_channel_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   
   $clickpost_username = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $clickpost_key = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   $clickpost_returnaddress = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_return_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   
   $origin_state= $postRequestParams['origin-state'];
   if($origin_state == 'Maharashtra') {
     $clickpost_latitude = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_latitude_mh', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     $clickpost_longitude = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_longitude_mh', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     $reseller_phone = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_reseller_phone_mh', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

	} else {
      $clickpost_latitude = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_latitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $clickpost_longitude = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_longitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 $reseller_phone = $this->scopeConfig->getValue('clickpost/clickpostservices/clickpost_reseller_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
  
              $clickpost_apiKey = $this->scopeConfig->getValue('addressautocomplete/general/google_api', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   
			  $dropaddress = $postRequestParams['destination-line-1'].','.$postRequestParams['destination-city'].'  '.$postRequestParams['destination-state'].','.$postRequestParams['destination-country'];
			  $prepAddr = str_replace(' ','+',$dropaddress);
             //$address = 'BTM 2nd Stage, Bengaluru, Karnataka 560076'; // Address
	         // Get JSON results from this request
			$geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($prepAddr).'&sensor=false&key='.$clickpost_apiKey);
			$geocode = json_decode($geocode, true); // Convert the JSON to an array
            if (isset($geocode['error_message'])){
              $errorMessage = $geocode['error_message'];
			  throw new \Exception($errorMessage);
			} 
			if (isset($geocode['status']) && ($geocode['status'] == 'OK')) {
			  $drop_lat = $geocode['results'][0]['geometry']['location']['lat']; // Latitude
			  $drop_long = $geocode['results'][0]['geometry']['location']['lng']; // Longitude
			} else {
			  $drop_lat = '';
			  $drop_long = '';
			}
			$account_code_value = $postRequestParams['account_code'];
			$account_code_option = explode(",", $account_code_value);
	        $courier_partner_id = trim($account_code_option[0]);
			$courier_partner_account_code = trim($account_code_option[1]);

			foreach ($requiredFields as $requiredField) {
                if (!isset($postRequestParams[$requiredField])) {
                    throw new \Exception('Missing required field - ' . $requiredField);
                }
            }

            $cod_collection_mode = 'cash';
            if (strtolower($postRequestParams['cod-amount']) === '0') {
                $cod_collection_mode = '';
            }

            $customerReferenceNumber = $postRequestParams['customer-reference-number'];
            $orderid = $postRequestParams['order-id'];
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
            $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderid);    
            //echo '<pre>';print_r($order->getData()); 
			$grandtotal_amount = $order->getGrandTotal();
			foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceIncrementID = $invoice->getIncrementId();
				$invoicecreateddate = $invoice->getCreatedAt();
				$invoicedate = $this->date->date($invoicecreateddate)->format('Y-m-d');
				}
			$destinationname = $postRequestParams['destination-name'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $salesOrder = $resource->getTableName('sales_order');
            $salesOrdersql = "SELECT * FROM " . $salesOrder . " WHERE increment_id = "."'$customerReferenceNumber'";
            $queryResult = $connection->fetchAll($salesOrdersql);

			$clickpost_waybill = $resource->getTableName('clickpost_waybill');
            $clickpostwaybillsql = "SELECT * FROM " . $clickpost_waybill . " WHERE order_increment_id = "."'$customerReferenceNumber'";
            $clickpostqueryResult = $connection->fetchRow($clickpostwaybillsql);
			

            $resultRedirect = $this->resultRedirectFactory->create();
            $errorMessage = '';
            $samePiece = false;
            if (array_key_exists('multiPieceCheck', $postRequestParams)) {
                $samePiece = true;
            }

		//$objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
		//$objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTimeFactory');
	    //$currentTimestamp = strtotime($objDate->formatDatetime(date("Y-m-d H:i:s")));//timestamp
		//$currentTimestamp = $objDate->formatDatetime(date("Y-m-d\TH:i:s.u\Z"));
		$invoicedate = $this->date->date($invoicecreateddate)->format('Y-m-d');
		$invoicetime = $this->date->date($invoicecreateddate)->format('H:i:s');
        $pickup_timeISO = $invoicedate.'T'.$invoicetime.'+0530';
		$description = 'Book Set';

            $dataToSendArray = [
                'consignments' =>  [
                    [
                        //'customer_code' => $this->_cookieManager->getCookie('customer-code'),
                        //'consignment_type' => $postRequestParams['consignment-type'],
                        //'service_type_id' => $postRequestParams['service-type'],
				        // 'account_code_id' => $postRequestParams['account_code'],
                        //'reference_number' => '',
                       // 'load_type' => $postRequestParams['courier-type'],
                        //'customer_reference_number' => $customerReferenceNumber,
                        //'commodity_name' => 'Other',
                       // 'num_pieces' => $postRequestParams['num-pieces'],
                        'pickup_info' =>  [
                            'pickup_name' => $postRequestParams['origin-name'],
				            'pickup_email' => $postRequestParams['origin-email'],
                            'pickup_phone' => $postRequestParams['origin-number'],
                            'alternate_phone' => (!empty($postRequestParams['origin-alt-number'])) ? $postRequestParams['origin-alt-number'] : $postRequestParams['origin-number'],
                            'pickup_address' => $postRequestParams['origin-line-1'],
				            //'pickup_time' => '2023-01-20T12:00:00Z',
							'pickup_time' => $pickup_timeISO,
                           // 'address_line_2' => $postRequestParams['origin-line-2'],
                            'pickup_pincode' => $postRequestParams['origin-pincode'],
                            'pickup_city' => $postRequestParams['origin-city'],
				             'tin' => $postRequestParams['origin-tin'],
				             'pickup_state' => $postRequestParams['origin-state'],
                            'pickup_country' => $postRequestParams['origin-country'],
							'pickup_lat' => "$clickpost_latitude",
                            'pickup_long' => "$clickpost_longitude"
				            
                        ],
                        'drop_info' =>  [
                            'drop_name' => $postRequestParams['destination-name'],
				            'drop_email' => $postRequestParams['destination-email'], 
                            'drop_phone' => $postRequestParams['destination-number'],
                            'alternate_phone' => (!empty($postRequestParams['destination-alt-number'])) ? $postRequestParams['destination-alt-number'] : $postRequestParams['destination-number'],
                            'drop_address' => $postRequestParams['destination-line-1'],
                            //'address_line_2' => $postRequestParams['destination-line-2'],
                            'drop_pincode' => $postRequestParams['destination-pincode'],
                            'drop_city' => $postRequestParams['destination-city'],
                            'drop_state' => $postRequestParams['destination-state'],
                            'drop_country' => $postRequestParams['destination-country'],
							'drop_lat' => "$drop_lat",
                            'drop_long' => "$drop_long"
                        ],
                         'shipment_details' =>  [
                            'height' => '12',
				            'weight' => $postRequestParams['weight']['0']* '1000', 
                            'length' => '10',
                            'breadth' => '10',
                            'order_type' => 'PREPAID',
                            'invoice_value' => round($grandtotal_amount),
                            'invoice_number' => $invoiceIncrementID,
                            'invoice_date' => $invoicedate,
                            'reference_number' => $customerReferenceNumber,
                            'cod_value' => '0',
							'courier_partner' => $courier_partner_id,
							'items' => [[
								'product_url' => '',
								'price' => round($grandtotal_amount),
								'weight' => $postRequestParams['weight']['0']* '1000',
								//'description' => $postRequestParams['description']['0'],
							    'description' => $description,
								'quantity' => $postRequestParams['quantity']['0'],
								'sku' => $postRequestParams['sku']['0'],
								'additional' =>[],
							   ]]

                          ],
						 'additional' =>  [
                            'label' => '1',
				            'return_info' => [
								'name' => $postRequestParams['origin-name'],
								'email' => $postRequestParams['origin-email'],
								'phone' => $postRequestParams['origin-number'],
								'address' => $postRequestParams['origin-line-1'],
								'pincode' => $postRequestParams['origin-pincode'],
								'city' => $postRequestParams['origin-city'],
								 'tin' => $postRequestParams['origin-tin'],
								 'state' => $postRequestParams['origin-state'],
								'country' => $postRequestParams['origin-country'],
								], 
                            'reseller_info' => [
								'name' => $reseller_name,
								'phone' => $reseller_phone,
								],
                            //'awb_number' => '',
                            'delivery_type' => 'FORWARD',
                            'async' => '',
                            'gst_number' => $postRequestParams['origin-tin'],
                            'account_code' => $courier_partner_account_code,
                            'from_wh' => 'From Warehouse',
                            'to_wh' => 'To Warehouse',
							'channel_name' => $channel_name,
							'order_date' => $invoicedate,
                            'enable_whatsapp' => '',
                            'is_fragile' => '1',
                            'is_dangerous' => '1',
                            'order_id' => $customerReferenceNumber,
                        ],

                        //'same_pieces' => $samePiece,
                        //'cod_favor_of' => '',
                        //'pieces_detail' => [],
                        //'cod_collection_mode' => $cod_collection_mode,
                        //'cod_amount' => $postRequestParams['cod-amount'],
                    ],
                ]
            ];
            if ($postRequestParams['num-pieces'] === 1 || $samePiece === true) {
                $temp_pieces_details =  [
                    'description' => $postRequestParams['description'],
                    'declared_value' => $postRequestParams['declared-value'],
                    'quantity' => $postRequestParams['quantity'],
                    'weight' => $postRequestParams['weight'],
                    'height' => $postRequestParams['height'],
                    'length' => $postRequestParams['length'],
                    'width' => $postRequestParams['width']
                ];
               //array_push($dataToSendArray['consignments'][0]['pieces_detail'], $temp_pieces_details);
            } else {
                for ($index = 0; $index < $postRequestParams['num-pieces']; $index++) {
                    $temp_pieces_details =  [
                        'description' => $postRequestParams['description'][$index],
                        'declared_value' => $postRequestParams['declared-value'][$index],
                        'quantity' => $postRequestParams['quantity'][$index],
                        'weight' => $postRequestParams['weight'][$index],
                        'height' => $postRequestParams['height'][$index],
                        'length' => $postRequestParams['length'][$index],
                        'width' => $postRequestParams['width'][$index]
                    ];
                    //array_push($dataToSendArray['consignments'][0]['pieces_detail'], $temp_pieces_details);
                };
            }
           
		   $dataToSendArrayfinal = $dataToSendArray['consignments'][0];
           $dataToSendJson = json_encode($dataToSendArrayfinal);
			//echo '<pre>';print_r($dataToSendArrayfinal); die;
            
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
			//Order Creation V3 API (Forward) code end
            
			//$result = curl_exec($ch);
            //curl_close($ch);
            if (array_key_exists('result', $resultdata) && array_key_exists('order_id', $resultdata['result']) && strlen($resultdata['result']['order_id']) > 0)
				{
               
				//echo '<pre>';print_r($resultdata);
				$referenceNumber = $resultdata["result"]["waybill"];
				$shipsy_tracking_url = $resultdata["result"]["label"];

				$courier_name = $resultdata["result"]["courier_name"];
				$courier_partner_id = $resultdata["result"]["courier_partner_id"];
				
                $storedReferenceNumbers = $queryResult[0]['shipsy_reference_numbers'];
                if (!empty($storedReferenceNumbers)) {
                    throw new \Exception('Order already synced. Reference Number - ' . $storedReferenceNumbers);
                }
	        $sqlq = "UPDATE ".$salesOrder." SET shipsy_reference_numbers='".$referenceNumber."',clickpost_courier_name ='".$courier_name."',shipsy_tracking_url='".$shipsy_tracking_url."' WHERE increment_id="."'$customerReferenceNumber'";
            $uquery = $connection->query($sqlq);
				
            $order = $this->_orderRepository->get($orderid);
            
				// to check order can ship or not
				if (!$order->canShip()) {
					throw new \Magento\Framework\Exception\LocalizedException(
					__('You cant create the Shipment of this order.') );
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
					$this->_shipmentNotifier->notify($orderShipment);
					$orderShipment->save();
				} catch (\Exception $e) {
					throw new \Magento\Framework\Exception\LocalizedException(
					__($e->getMessage())
					);
				}
			if(empty($clickpostqueryResult)) {
				$InsertAwb = $objectManager->create('Centralbooks\ClickpostExtension\Model\Awb');
				$InsertAwb->setWaybill($referenceNumber);
				$InsertAwb->setCourierName($courier_name);
				$InsertAwb->setCourierPartnerId($courier_partner_id);
				$InsertAwb->setState(1);
				$InsertAwb->setStatus("AwbRegistered");
				$InsertAwb->setOrderid($orderid);
				$InsertAwb->setOrderIncrementId($customerReferenceNumber);
				$InsertAwb->setShipmentTo($destinationname);
				$InsertAwb->setShippingAmount($postRequestParams['declared-value']['0']);
				$InsertAwb->setShipmentId($shipmentId);
				$InsertAwb->setShipmentLength($postRequestParams['length']['0']);
				$InsertAwb->setShipmentWidth($postRequestParams['width']['0']);
				$InsertAwb->setLocation($origin_state);
				$InsertAwb->setShipmentHeight($postRequestParams['height']['0'])->save();
				$InsertAwb->save();
			
			} else {
				$awbid = $clickpostqueryResult['awb_id'];
				$updateAwb = $objectManager->create('Centralbooks\ClickpostExtension\Model\Awb')->load($awbid);
				$updateAwb->setWaybill($referenceNumber);
				$updateAwb->setCourierName($courier_name);
				$updateAwb->setCourierPartnerId($courier_partner_id);
				$updateAwb->setState(1);
				$updateAwb->setStatus("AwbRegistered");
				$updateAwb->setOrderid($orderid);
				$updateAwb->setOrderIncrementId($customerReferenceNumber);
				$updateAwb->setShipmentTo($destinationname);
				$updateAwb->setShippingAmount($postRequestParams['declared-value']['0']);
				$updateAwb->setShipmentId($shipmentId);
				$updateAwb->setShipmentLength($postRequestParams['length']['0']);
				$updateAwb->setShipmentWidth($postRequestParams['width']['0']);
				$updateAwb->setLocation($origin_state);
				$updateAwb->setShipmentHeight($postRequestParams['height']['0'])->save();
				$updateAwb->save();
               }

			    $this->_messageManager->addSuccessMessage('Sync successful. Clickpost Reference Number for #'.$postRequestParams["customer-reference-number"]. ' - ' . $referenceNumber);
                return $resultRedirect->setPath('sales/order/index');
            } else {
                if (array_key_exists('meta', $resultdata) && array_key_exists('message', $resultdata['meta'])) {
                    $errorMessage = $resultdata['meta']['message'];
                } elseif (array_key_exists('error', $resultdata)) {
                    $errorMessage = $resultdata['meta']['message'];
                }
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage('Failed to sync order - '. $e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
        }
    }
}
