<?php 

namespace Retailinsights\Orders\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\AuthorizationInterface;

use \Magento\Shipping\Model\Config;

class OrderInformation extends Column
{
	/**
     * @var AuthorizationInterface
     */
    private $backendHelper;
    protected $_authorization;
    protected $formKey;
    protected $urlBuider;
    protected $postFactory;
    protected $_deliveryModelConfig;
    protected $shippingAllmethods;
    protected $scopeConfig;
    protected $shipconfig;
    public $_storeManager;
    protected $order;
    protected $_shipmentCollection;
    protected $_orderRepository;
    protected $_searchCriteria;
       /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    private $driverCollectionFactory;
    private $autoDriverCollection;
    private $fedexPincodeCollectionFactory;
    private $postCollectionFactory;
    protected $deliveryboy;
	protected $logger;

    public function __construct(
        \Magento\Backend\Helper\Data $backendHelper,
    	AuthorizationInterface $authorization,
        \Retailinsights\FedexPincode\Model\ResourceModel\FedexPincodeList\CollectionFactory $fedexPincodeCollectionFactory,
        \Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory $fedexLabel,
		\Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
		\Ecom\Ecomexpress\Model\ResourceModel\Awb\CollectionFactory $ecomcollectionFactory,
		\Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $urlBuilder,
		\Psr\Log\LoggerInterface $logger,
        \Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers\CollectionFactory $autoDriverCollection,
        \Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\CollectionFactory $driverCollectionFactory,
        \Retailinsights\Orders\Model\PostFactory $postFactory,
        \Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
         Config $deliveryModelConfig,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollection,
        ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, array $components = [], array $data = [])
    {
        $this->backendHelper = $backendHelper;
    	 $this->_authorization = $authorization;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->fedexPincodeCollectionFactory = $fedexPincodeCollectionFactory;
        $this->fedexLabel = $fedexLabel;
		$this->collectionFactory = $collectionFactory;
		$this->ecomcollectionFactory = $ecomcollectionFactory;
		$this->deliveryboy = $deliveryboy;
         $this->formKey = $formKey;
        $this->urlBuilder = $urlBuilder;
		$this->logger = $logger;
        $this->autoDriverCollection = $autoDriverCollection;
        $this->driverCollectionFactory = $driverCollectionFactory;
        $this->postFactory = $postFactory;
        $this->shippingAllmethods = $shippingAllmethods;
        $this->_deliveryModelConfig = $deliveryModelConfig;
        $this->shipconfig=$shipconfig;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager=$storeManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;
        $this->_shipmentCollection = $shipmentCollection;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $deliveryMethods = $this->_deliveryModelConfig->getActiveCarriers();
        $deliveryMethodsArray = array();
        foreach ($deliveryMethods as $shippigCode => $shippingModel) {
            $shippingTitle = $this->scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            $deliveryMethodsArray[$shippigCode] = array(
                'label' => $shippingTitle,
                'value' => $shippigCode
            );
        }
        // return $deliveryMethodsArray;


        foreach ($deliveryMethodsArray as $key => $value) {
            
            $ship[]=$value['label'];
            $value_ship[]=$value['value'];   
        }
        $shipping_methods = array_unique($ship);
        $shipping_value = array_unique($value_ship);

		//echo '<pre>'; print_r($shipping_methods);die;
        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $shipments = $this->_shipmentCollection->create()->addFieldToFilter('order_id', $item["entity_id"])->setOrder('entity_id', 'DESC');
                
                $userwebsite=  $this->backendHelper->getHomePageUrl();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('core_config_data');
                $sql = $connection->select()->from($tableName)->where('path = ?', 'admin/url/custom');
                $result = $connection->fetchAll($sql); 
                $url1 = $result[0]['value'];
                $url = $result[0]['value'].'cbsadmin/retailinsights_admin/index/Display';
                
                $ship='';
                 $cboLabel = '';
                 $slip = '';
                 $Cancel = '';
                 foreach ($shipments as $value) {
                    $ship = 'Shipped '.substr($value['total_qty'], 0, -5);
                    $orderdetails = $this->order->create()->loadByIncrementId($item['increment_id']);
					//echo '<pre>'; print_r($orderdetails->getData());die;
                    //$shipping_key = explode('_', $orderdetails->getData('shipping_method'));
					$shipping_key = $orderdetails->getData('shipment_type');
					
                    $slip = 'Packing Slip';


                    //if(($shipping_key[0] == 'centralbooksshipping')){
					if(($shipping_key == 'cboshipping')){
                        $cboLabel = '<p>CBO Shipment</p><br/><p>Processing is Pending</p>';
                        if(($item['status'] == 'complete') || ($item['status'] == 'order_not_delivered')){
                        $this->logger->info($item['entity_id']); 
                        $this->logger->info($item['status']); 
                        	if($this->_authorization->isAllowed('Magento_Sales::actions_edit')){
                            		$Cancel = '<a href="'.$url."?id=".$item["entity_id"]."&value=abc&key=cancel_shipment".'">Cancel Shipment</a><br>';
								}else{
                        			$Cancel='';
								}

                            // $Cancel='Cancel Shipment';
                        }else{
                            $Cancel='';
                        }

                    }
                    // if($shipping_key[0] == 'centralbooksshipping'){
                    //     $storeLabel = "<p>Store PickUp Order<p>";
                    // }
                 }

		    
                // get fedex tracking no.
                $FedexTrackingLabel = '';
                $trackingNo = $this->fedexLabel->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('order_id',$item["entity_id"]);
                if(!empty($trackingNo->getFirstItem()->getData('order_increment_id'))){
                    
                    $FedexTrackingNo = $trackingNo->getFirstItem()->getData('trackingnumber');
                    if($FedexTrackingNo == ''){
                        $FedexTrackingLabel = '<p>Fedex Shipment</p><p style="color:red;">Label not generated</p>';
                        $Cancel = '';
                    }else{
                        foreach($trackingNo as $track){
                            $FedexTrackingNo = $track->getData('trackingnumber');
                            $pdfUrl = $url1."cbsadmin/infomodus_fedexlabel/pdflabels/one/label_name/".$track->getData('labelname');
                        
                            $formKey = $this->formKey->getFormKey();
                            $form = '<form method="post" enctype="multipart/form-data" action="'.$pdfUrl.'">
                            <input type="hidden" name="selected[]" value="'.$item["entity_id"].'">
                            <input type="hidden" name="filters[placeholder]" value="true">
                            <input type="hidden" name="search" value="">
                            <input type="hidden" name="namespace" value="sales_order_grid">
                            <input type="hidden" name="form_key" value="'.$formKey.'">
                           <button id="fedexpdf_btn" style="border: none;
                                                background-color: inherit;
                                                font-size: 12px;
                                                padding: inherit;
                                                cursor: pointer;
                                                display: inline-block;
                                                text-align: left;" class="btn success invoice_btn">Fedex '.$track->getData('type').' labels</button>
                                                </form> ';
                                                
                            $FedexTrackingLabel .= '<p>FedEx-'.$track->getData('type').' : '.$FedexTrackingNo.'</p>'.$form.'</br>';
                        }
                        if(($item['status'] == 'complete') || ($item['status'] == 'order_not_delivered')) {
                            if($this->_authorization->isAllowed('Magento_Sales::actions_edit')){
                                    $Cancel = '<a href="'.$url."?id=".$item["entity_id"]."&value=abc&key=cancel_shipment".'">Cancel Shipment</a><br>';
                                }else{
                                    $Cancel='';
                                }

                            // $Cancel='Cancel Shipment';
                        }else{
                            $Cancel='';
                        }
                    }
                   
                    $cboLabel = '';
                }

                $order_qty  = $this->_orderRepository->get($item["entity_id"]);

                $totalQty =$order_qty->getTotalItemCount(); 
                $orderQty = $this->getOrderedQty($order_qty);

                foreach ($order_qty->getAllVisibleItems() as $itemNew) {
                    $name= $itemNew->getName();
                    $item['product_name'] = $name;
                }


                $invoicesdCount = $this->getInvoicedCount($order_qty);
                $status = $order_qty->getData("gift_message_id");

                $Ordered = 'Ordered'.$orderQty;
                
                if($invoicesdCount != 0){

                    $Invoiced=' Invoiced '.$invoicesdCount;
                    $Packing='Packing Slip';
                }else{
                    $Invoiced='';
                    $Packing='';
                }
                // if($item['status'] != 'complete' && $item['status'] != 'canceled'){
                //     $Cancel='Cancel Shipment';
                // }else{
                //     $Cancel='';
                // }

                // $url= $this->_storeManager->getStore()->getBaseUrl();

                $orderId = $item["entity_id"];

                $orderdetails = $this->order->create()->loadByIncrementId($item['increment_id']);
                $shipMethod = $orderdetails->getShippingMethod();
                $storeLabel = '';
                if($shipMethod == 'cistorepickup_cistorepickup'){
                    $storeLabel = "<p style='color:red; font-weight:bolder;'>Store PickUp<p>";
                }


                $orderdetails->getGrandTotal(); //you can get the grandtotal like this
                $invoice_id = '';
                foreach ($orderdetails->getInvoiceCollection() as $invoice)
                    {
                        $invoice_id = $invoice->getIncrementId();
                    }
              

                $collection= $this->postCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('order_id', $item["entity_id"]);
                $slip_count='';
                $count = 0;

                if($collection->getFirstItem()->getData('id')){
                    $count = $collection->getFirstItem()->getData('invoice_count');
                }else{
                    $count = 0;
                }
                if($count > 0){
                    $slip_count='('.$count.')';
                }else{
                    $slip_count='';
                }
                $link = '';
                
                if($invoicesdCount > 0 && $item["status"] == 'assigned_to_picker'){
                    $shipping_is_available = 1;
                }else{
                    $shipping_is_available = 0;
                }
                if($shipping_is_available == 1){
					//echo '<pre>'; print_r($shipping_methods);die;
                    foreach($shipping_methods as $key => $value){
                        if(($value != 'Free Shipping') && ($value != 'StorePickUp') && ($value != 'Delhivery Lastmile') && ($value != 'ecomexpress') && ($value != 'DTDC') && ($value != 'Centralbooks Handling')) {

                            ${'ship'.$key} = $shipping_methods[$key];
                            ${'ship_id'.$key} = $shipping_value[$key];

                            //hiding CEntralbook Shipping Link**
                            if($this->_authorization->isAllowed('Magento_Sales::actions_edit')){
                            	$link .=  '<a href="'.$url."?id=".$item["entity_id"]."&value=".${'ship_id'.$key}."&key=shipping".'">'.${'ship'.$key}.'</a><br>';
							}else{
								$link .= '';
							}

                        }
                        
                    }

                   /** // Check for available fedex pincodes
                    $isFedexAvailable = $this->ChechFedexService($item["entity_id"]); 
                    if($isFedexAvailable == "true"){

					if($this->_authorization->isAllowed('Magento_Sales::actions_edit')){
						$link .=  '<a href="'.$url.'?id='.$item["entity_id"].'&value=fedexlabel&key=shipping">FedEx Shipping</a><br>';
					}else{
						$link .= '';
					}

                    }else{
                        $link .=  '<p style="color:red;">Pincode not available</p><br>';
                    }**/
                }else{
                    $ship_id0 = $ship0 ='';
                    $ship_id1 = $ship1 ='';
                    $ship_id2 = $ship2 ='';
                    $ship_id3 = $ship3 ='';
                    $ship_id4 = $ship4 ='';
                    $ship_id5 = $ship5 ='';
                }
                $url_admin = $url1.'cbsadmin/retailinsights_orders/post/Index/';

                $driverData = $this->getDriverInformation($orderdetails);
                if($driverData['customColumn'] != ''){
                    $cboLabel = $driverData['customColumn'];
                }

				$trackingData = $this->getTrackingInformation($orderdetails);
                if($trackingData['trackingcustomColumn'] != ''){
                    $cboLabel = $trackingData['trackingcustomColumn'];
                }

			 foreach($shipping_methods as $key => $value){
               if($value == 'Delhivery Lastmile') {

				   if(($item['status'] == 'complete') || ($item['status'] == 'order_not_delivered')){
					   if($this->_authorization->isAllowed('Magento_Sales::actions_edit')){
						$Cancel = '<a href="'.$url."?id=".$item["entity_id"]."&value=abc&key=cancel_shipment".'">Cancel Shipment</a><br>';
					   } else{ $Cancel='';}
				   }

			   }

			   if($value == 'ecomexpress') {

				   if(($item['status'] == 'complete') || ($item['status'] == 'order_not_delivered')){
					   if($this->_authorization->isAllowed('Magento_Sales::actions_edit')){
						$Cancel = '<a href="'.$url."?id=".$item["entity_id"]."&value=abc&key=cancel_shipment".'">Cancel Shipment</a><br>';
					   } else{ $Cancel='';}
				   }

			   }
             }

                $invoiceKey = $url1.'cbsadmin/sales/order/pdfinvoices/';

                $formKey = $this->formKey->getFormKey();

                $trackingNumber = $this->getOrderInfo($orderdetails);
               // $item['trackingColumn'] = html_entity_decode($driverData['trackingColumn']);
			    if(!empty($trackingNumber['trackingColumn'])) {
				    $item['trackingColumn'] = html_entity_decode($trackingNumber['trackingColumn']);
			    }
                $item['dispatched_on'] = $trackingNumber['dispatched_on'];

                // cancel shipment check
                $isCanceledShip = '';
                if($orderdetails->getData('canceled_shipment') == 1){
                    $isCanceledShip = '<p style="color:red; font-weight:600">Previous Shipment Canceled</p>';
                } else{
                    $isCanceledShip = '';
                }
                
                if($this->_authorization->isAllowed('Magento_Sales::actions_edit')){
                		$slip_label = $slip;
                		$slip_count_label = $slip_count;
                }else{
                		$slip_label = '';
                		$slip_count_label = '';
				}
                $item[$this->getData('name')] = html_entity_decode('
                        '.$storeLabel.'
                        <p>'.$Ordered.'</p><br>
                        <p>'.$Invoiced.'</p><br>
                        <p>'.$ship.'</p><br>'.$FedexTrackingLabel.$cboLabel.$Cancel.'
                        <form method="post" enctype="multipart/form-data" action="'.$invoiceKey.'">
                            <input type="hidden" name="selected[]" value="'.$item["entity_id"].'">
                            <input type="hidden" name="filters[placeholder]" value="true">
                            <input type="hidden" name="search" value="">
                            <input type="hidden" name="namespace" value="sales_order_grid">
                            <input type="hidden" name="form_key" value="'.$formKey.'">
                            <button id="invoice_btn" style="border: none;
                                                background-color: inherit;
                                                font-size: 12px;
                                                padding: inherit;
                                                cursor: pointer;
                                                display: inline-block;
                                                text-align: left;" class="btn success invoice_btn">'.$slip_label.$slip_count_label.'</button>
                        </form> '.$isCanceledShip.$link
                    );
			
            }
        }
        
        return $dataSource;
    }

    public function getInvoicedCount($order)
    {
        $orderId = $order->getId();
        $searchCriteria = $this->_searchCriteria->addFilter('order_id', $orderId)->create();
        try {
            $invoices = $this->invoiceRepository->getList($searchCriteria);
            $totalInvoice = $invoices->getTotalCount();
        } catch (Exception $exception)  {
            $this->logger->critical($exception->getMessage());
            $totalInvoice = 0;
        }
        return $totalInvoice;
    }
    public function getOrderedQty($order)
    {
        $qty = 0;
        foreach ($order->getAllItems() as $item) {
            $qty= $qty + intval($item->getQtyOrdered());
        }
        return $qty;
    }

    public function getDriverInformation($order)
    {
			// $labelText = [];
			$labelText['customColumn'] = '';
			$labelText['trackingColumn'] = '';
			$orderId = $order->getId();
          
           $pobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
		    //$presource = $pobjectManager->get('\Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder'); 
		    $presource = $pobjectManager->get('Magento\Framework\App\ResourceConnection');
			$pconnection = $presource->getConnection();
			$ptableName = $presource->getTableName('ci_stores_order');
			$psql = $pconnection->select()->from($ptableName)->where('order_id = ?', $orderId);
			$pickupresult = $pconnection->fetchRow($psql); 
			  
		   if($pickupresult) {
			   $order_status = $pickupresult['order_status']; 
              if($order_status == 'order_delivered') {
			     $store_name = $pickupresult['store_name'];
				 $pickup_person_name = $pickupresult['pickup_person_name'];
				 $given_person = $pickupresult['given_person'];
				  $labelText['customColumn'] = "<p>Store name: ".$store_name."</p>" ."<p>Pickup Person name: ".$pickup_person_name."</p>"."<p>Given Person:".$given_person."</p>";
			  }
			}
            $drivers = $this->driverCollectionFactory->create()
               ->addFieldToSelect('*')
               ->addFieldToFilter('order_id', $orderId);

            if($drivers) {
              if($drivers->getFirstItem()) {
                 $driverId = $drivers->getFirstItem()->getData('driver_id');
				 $deliveryboyId = $drivers->getFirstItem()->getData('deliveryboy_id');

                if(!empty($driverId)){
                   $autodrivers = $this->autoDriverCollection->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('id', $driverId);
				      if($autodrivers->getFirstItem()->getData()) {
                        $info = $autodrivers->getFirstItem();
                        $labelText['customColumn'] = "<p>CBO Shipment</p><br/><p>Driver Name: ".$info->getData('driver_name')."</p><br>".
                        "<p>Driver Mobile: ".$info->getData('driver_mobile')."</p><br>".
                        "<p>Auto Number: ".$info->getData('auto_number')."</p><br>";
                        $labelText['trackingColumn'] = "<p>".$info->getData('driver_name')." : ".
                        $info->getData('driver_mobile')."</p><br> Auto:<p>".
                        $info->getData('auto_number')."</p>";
						

				/*} elseif($drivers->getFirstItem()->getData('tracking_title') == 'Delhivery'){
                  $name = $drivers->getFirstItem()->getData('tracking_title');
                    $number = $drivers->getFirstItem()->getData('tracking_number');
                   $labelText['customColumn'] = "<br/>".
							"<p>Shipment name: ".$name."</p>" ."<p>Number: ".$number."</p>";
                */
				
                } elseif(!empty($drivers->getFirstItem()->getData('tracking_title'))){
                    $name = $drivers->getFirstItem()->getData('tracking_title');
                    $number = $drivers->getFirstItem()->getData('tracking_number');
                    $labelText['customColumn'] = "<br/>".
							"<p>Shipment name: ".$name."</p>" ."<p>Number: ".$number."</p>";
						//$labelText['trackingColumn'] = "<p>".$name." : ".$number."</p>";
                 }
               }  elseif(!empty($deliveryboyId)) {
                        $deliveryboy = $this->deliveryboy->load($deliveryboyId);
						$deliveryboyName = $deliveryboy->getName();
                        $deliveryboyContact = $deliveryboy->getMobileNumber();
						$deliveryvehicleNumber = $deliveryboy->getVehicleNumber();
                        $labelText['customColumn'] = "<p>CBO Shipment</p><br/><p>Driver Name: ".$deliveryboyName."</p><br>".
                        "<p>Driver Mobile: ".$deliveryboyContact."</p><br>".
                        "<p>Auto Number: ".$deliveryvehicleNumber."</p><br>";
                        $labelText['trackingColumn'] = "<p>".$deliveryboyName." : ".
                        $deliveryboyContact."</p><br> Auto:<p>".
                        $deliveryvehicleNumber."</p>";
				    } 

					
           }
		}

        return $labelText;        
    }


	  public function getTrackingInformation($order){
		$TrackinglabelText['trackingcustomColumn'] = '';
        $TrackinglabelText['trackingColumn'] = '';
        $orderId = $order->getId();
		$deltrackingNo = $this->collectionFactory->create()
					->addFieldToSelect('*')
					->addFieldToFilter('orderid',$orderId);
		 //print_r($deltrackingNo->getData());
		 if(!empty($deltrackingNo->getFirstItem()->getData('order_increment_id'))){
			$delhiveryTrackingNo = $deltrackingNo->getFirstItem()->getData('awb');
			if($delhiveryTrackingNo == ''){
			  $TrackinglabelText['trackingcustomColumn'] = '<p>Delhivery Shipment</p><p style="color:red;">not created</p>';
			} else {
			  foreach($deltrackingNo as $track){
				 $name = 'Delhivery';
                 $number = $track->getData('awb');
                 $TrackinglabelText['trackingcustomColumn']  = "<br/>".
							"<p>Shipment name: ".$name."</p>" ."<p>Number: ".$number."</p>";
			  }
			}
		}

		$ecomtrackingNo = $this->ecomcollectionFactory->create()
					->addFieldToSelect('*')
					->addFieldToFilter('orderid',$orderId);
         //print_r($ecomtrackingNo->getData());die;
		if(!empty($ecomtrackingNo->getFirstItem()->getData('orderid'))){
			$ecomexpressTrackingNo = $ecomtrackingNo->getFirstItem()->getData('awb');
			if($ecomexpressTrackingNo == ''){
			  $TrackinglabelText['trackingcustomColumn'] = '<p>Ecomexpress Shipment</p><p style="color:red;">not created</p>';
			} else {
			  foreach($ecomtrackingNo as $track){
				 $name = 'Ecomexpress';
                 $number = $track->getData('awb');
                 $TrackinglabelText['trackingcustomColumn']  = "<br/>".
							"<p>Shipment name: ".$name."</p>" ."<p>Number: ".$number."</p>";
			  }
			}
		}
		if (!empty($order->getCboReferenceNumber())) {

			$courierName  = $order->getCboCourierName();
			$referenceNo  = $order->getCboReferenceNumber();

			$TrackinglabelText['trackingcustomColumn'] = 
				"<br/>" .
				"<p>Shipment Name: " . $courierName . "</p>" .
				"<p>Number: " . $referenceNo . "</p>";
		}


		 return $TrackinglabelText;

      }

    public function getOrderInfo($order)
    {
        $data['dispatched_on'] = '';
		$data['trackingColumn'] = '';
        $orderId = $order->getId();
        $drivers = $this->driverCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $orderId);
        if($drivers){
            $data['dispatched_on'] = $drivers->getFirstItem()->getData('created_at');
			$data['trackingColumn'] = $drivers->getFirstItem()->getData('tracking_number');
           
        }
        return $data;
    }

    public function ChechFedexService($orderId)
    {
        $order  = $this->_orderRepository->get($orderId);
        $postcode = $order->getShippingAddress()->getPostcode();
        
        $fedexPincode = $this->fedexPincodeCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('pincode', $postcode)
                ->addFieldToFilter('serviceable', 'Yes');
        if($fedexPincode->getFirstItem()->getData('pincode')){
            return 'true';
        }
        return 'false';
    }
}
?>
