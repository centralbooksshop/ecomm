<?php

namespace SchoolZone\Customer\Block\HODashboard;

use Magento\Framework\App\ResourceConnection;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $overallOrders;
    protected $scopeConfig;
    protected $resourceConnection;
    protected $returnsData;
    protected $returnsFactory;

	/**
	 * @param \Magento\Framework\App\CacheInterface $cache
	 * @param \Magento\Framework\Serialize\SerializerInterface $serializer
	 */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection,
        \Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
        \Ecom\Ecomexpress\Model\ResourceModel\Awb\CollectionFactory $ecomcollectionFactory,
        \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboy,
        \Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers\CollectionFactory $autoDriverCollection,
        \Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\CollectionFactory $driverCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Framework\App\CacheInterface $cache,
	    \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Plumrocket\RMA\Model\ReturnsFactory $returnsFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->collectionFactory = $collectionFactory;
        $this->ecomcollectionFactory = $ecomcollectionFactory;
        $this->deliveryboy = $deliveryboy;
        $this->autoDriverCollection = $autoDriverCollection;
        $this->driverCollectionFactory = $driverCollectionFactory;
        $this->orderFactory = $orderFactory;
		$this->cache = $cache;
        $this->serializer = $serializer;
        $this->returnsFactory = $returnsFactory;
    }

    public function ordersCountAndLink($status, $paymentMethod = null, $paymentStatus = null, $timeSpan = null, $shippingMethod = null)
    {    $starttime = microtime(true);
        if (!$this->overallOrders) {
            $highestTime = $this->scopeConfig->getValue('schooldashboard/ordertime/highest_orders_time');
            $highDate = (new \DateTime())->modify($highestTime)->format('Y-m-d h:i:s');
			//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$school_id = $this->getSchoolNameSessionValue();
			//$school_id = '1357';
            $connection = $this->resourceConnection->getConnection();
            //$this->overallOrders = $connection->fetchAll("SELECT so.entity_id, so.school_id, so.increment_id, so.status, so.payment_status, so.created_at, so.shipping_method, sop.method from sales_order as so join sales_order_payment as sop on so.entity_id = sop.parent_id where created_at >= '$highDate' AND so.school_id = '$school_id';");
			$this->overallOrders = $connection->fetchAll("SELECT so.entity_id, so.school_id, so.increment_id, so.status, so.payment_status, so.created_at, so.shipping_method, sop.method from sales_order as so join sales_order_payment as sop on so.entity_id = sop.parent_id where created_at >= '$highDate' AND (so.school_id IN($school_id)) AND (`status` NOT IN('order_split'));");
        }
        $count = 0;
        $params = [
            'status' => $status,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'time' => $timeSpan,
            'shipping_method' => $shippingMethod
        ];
        $filters = array_filter($params, fn($value) => !is_null($value) && $value !== '');

        $orders = $this->overallOrders;
        if ($status) {
            $orders = array_filter($orders, fn($value) => $value['status'] == $status);
        }
        
        if ($shippingMethod) {
            $orders = array_filter($orders, fn($value) => $value['shipping_method'] == $shippingMethod);
        }

        $orders = $this->addTimeSpan($timeSpan, $orders);
        if ($paymentStatus) {
            $orders = array_filter($orders, fn($value) => $value['payment_status'] == $paymentStatus);
        }
        if ($paymentMethod) {
            $orders = array_filter($orders, fn($value) => $value['method'] == $paymentMethod);
        }
        $count = count($orders);
        $countText = ($count) ? $count : '';
        $url = $this->getUrl('schoolzone_customer/hodashboard/orders', $filters);

		$endtime = microtime(true); // when function ends
        $duration  = $endtime - $starttime;
		echo '<!--OrdersCountAndLink start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';

        return ["link" => "<a href='".$url."'>".$countText."</a>", "count" => $count, "per" => $this->percentageOfOrders($count)."%"];
    }

    private function addTimeSpan($timeSpan, $orders)
    {   
		$starttime = microtime(true);
        $highestTime = $this->scopeConfig->getValue('schooldashboard/ordertime/highest_orders_time');
        if ($timeSpan == 'below-3') {
            $date2 = (new \DateTime())->modify('-72 hours')->format('Y-m-d h:i:s');
            $orders = array_filter($orders, function($value) use($date2) { return $value['created_at'] > $date2; });
        } elseif($timeSpan == 'above-3') {
            $date = (new \DateTime())->modify($highestTime)->format('Y-m-d h:i:s');
            $date1 = (new \DateTime())->modify('-72 hours')->format('Y-m-d h:i:s');
            $orders = array_filter($orders, function($value) use($date, $date1) { return $value['created_at'] >= $date && $value['created_at'] < $date1; });
        } else {
            $date = (new \DateTime())->modify($highestTime)->format('Y-m-d h:i:s');
            $orders = array_filter($orders, function($value) use($date) { return $value['created_at'] >= $date; });
        }

		$endtime = microtime(true); // when function ends
        $duration  = $endtime - $starttime;
		echo '<!--addTimeSpan start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';
        return $orders;
    }

    protected function overallOrders()
    {
        return $this->ordersCountAndLink(null)['count'];
    }

    private function percentageOfOrders($count)
    {
        if (count($this->overallOrders)) {
            return ($count/count($this->overallOrders))*100;
        } else {
            return 0;
        }
    }

    public function dispatchedOrdersTabularData()
    {
        $starttime = microtime(true);
		$data = $filteredData = [];
        $html = '';
		$highestTime = $this->scopeConfig->getValue('schooldashboard/ordertime/highest_orders_time');
        $highDate = (new \DateTime())->modify($highestTime)->format('Y-m-d h:i:s');
		//$school_id = '1357';
        $connection = $this->resourceConnection->getConnection();
		$school_id = $this->getSchoolNameSessionValue();
		
        //$orders = $connection->fetchAll("SELECT so.entity_id, so.increment_id, so.status, so.created_at from sales_order as so where so.status = 'dispatched_to_courier' AND so.created_at >= '$highDate';");

		$orders = $connection->fetchAll("SELECT DISTINCT so.order_id, so.increment_id, so.shipsy_reference_numbers, so.status, so.school_id, so.created_at, cas.driver_id ,cas.deliveryboy_id, cas.tracking_title , cas.tracking_number , cba.driver_name , cba.driver_mobile , cba.auto_number , dboy.name , dboy.mobile_number , dboy.vehicle_number , ecoma.awb, ecoma.orderid, dla.awb, dla.order_increment_id , cso.order_status , cso.store_name , cso.pickup_person_name , cso.given_person
		FROM
		   (
		   SELECT DISTINCT so.entity_id AS order_id , so.increment_id, so.shipsy_reference_numbers, so.status, so.school_id, so.created_at FROM sales_order as so where so.status = 'dispatched_to_courier' AND so.created_at >= '$highDate' AND (so.school_id IN($school_id))
		   ) AS so 
		 LEFT JOIN ci_stores_order AS cso ON so.order_id = cso.order_id
		 LEFT JOIN cbo_assign_shippment AS cas ON so.order_id = cas.order_id 
		 LEFT JOIN cboshipping_autodrivers AS cba ON cba.id = cas.driver_id
		 LEFT JOIN deliveryboy_deliveryboy AS dboy ON dboy.id = cas.deliveryboy_id
		 LEFT JOIN delhivery_lastmile_awb AS dla ON dla.orderid = so.order_id
         LEFT JOIN ecomexpress_awb AS ecoma ON ecoma.orderid = so.order_id
		 ");

		 //echo '<pre>';print_r($orders);die;

        $fullOrders = $orders;
        $fullOrders = $this->addTimeSpan(null, $fullOrders);
        $finalData = [];
        foreach($fullOrders as $key => $eachOrder) {
			//echo '<pre>';print_r($eachOrder);
            if($driverInformation = $this->getMainDriverInformation($eachOrder)) {
                $fullOrders[$key]['shipping_method'] = 'CBO Shipment';
                $fullOrders[$key]['tracking'] = $driverInformation;
            } else {
                //$orderInfo = $this->orderFactory->create()->load($eachOrder['order_id']);
                $trackingInfo = $this->getMainTrackingInformation($eachOrder);
				$fullOrders[$key]['shipping_method'] = $trackingInfo['shipment'];
                $fullOrders[$key]['tracking'] = $trackingInfo['tracking'];
            }
		   
            $finalData[$fullOrders[$key]['shipping_method']][] = $fullOrders[$key];
        }

		foreach ($finalData as $shipment => $values) {
            $html.= '<tr><td class="shipment-click" data-shipment="'.str_replace(' ', '-', strtolower($shipment)).'">'.$shipment.'</td><td>'.count($this->addTimeSpan('below-3', $values)).'</td><td>'.count($this->addTimeSpan('above-3', $values)).'</td><td>'.count($values).'</td></tr>';
            foreach ($values as $key => $value) {
                //$url = $this->getUrl('sales/order/view', ['order_id' => $value['order_id']]);
				$url = 'javascript:void(0)';
                $trackingInfo = '<a href="'.$url.'">'.$value['increment_id'].'</a> -- ';
                $html.= '<tr style="display: none;" class="'.str_replace(' ', '-', strtolower($shipment)).'"><td style="text-align: left;" colspan="6">'.$trackingInfo.$value['tracking'].'</td></tr>';
            }
        }
        $html.= '<tr style="font-weight: 700; background-color: #b7f07f;"><td>Grand Total</td><td>'.$this->ordersCountAndLink('dispatched_to_courier', null, null,'below-3')['link'].'</td><td>'.$this->ordersCountAndLink('dispatched_to_courier', null, null,'above-3')['link'].'</td><td>'.$this->ordersCountAndLink('dispatched_to_courier', null, null, null)['link'].'</td></tr>';
        
        $cacheKey  = 'CustomDashboard'.'-'.'reports';
		$cacheTag  = '';
		$cacheData = $this->cache->load($cacheKey);
		if ($cacheData) {
             $htmlData = $this->serializer->unserialize($cacheData);
			 $html = json_decode($htmlData, TRUE);
			//echo '<pre>';print_r($html);
        } else {
            $cachehtmlData = json_encode($html);
			$this->cache->save(
				$this->serializer->serialize($cachehtmlData),
				$cacheKey,
				[$cacheTag],
				86400
			);
		 }

		$endtime = microtime(true); // when function ends
        $duration  = $endtime - $starttime;
		echo '<!--dispatchedOrdersTabularData start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';
		return $html;
    }
    
	public function getMainTrackingInformation($orderpdetail)
	{
        
		$starttime = microtime(true);
        $TrackinglabelText = [];
		if($orderpdetail) {
			$order_increment_id = $orderpdetail['order_increment_id'];
			if(!empty($order_increment_id)){
				$delhiveryTrackingNo = $orderpdetail['awb'];
				if($delhiveryTrackingNo == ''){
				  $TrackinglabelText['tracking'] = '<p>Delhivery Shipment</p><p style="color:red;">not created</p>';
				} else {
				  //foreach($deltrackingNo as $track){
					 $name = 'Delhivery';
					 $number = $orderpdetail['awb'];
					 $TrackinglabelText['tracking']  = "<p>Shipment name: ".$name.", Number: ".$number."</p>";
					$TrackinglabelText['shipment'] = $name;
				  //}
				}
			}
		
			if(!empty($orderpdetail['orderid'])) {
				$ecomexpressTrackingNo = $orderpdetail['awb'];
				if($ecomexpressTrackingNo == ''){
				  $TrackinglabelText['tracking'] = '<p>Ecomexpress Shipment</p><p style="color:red;">not created</p>';
				} else {
				  //foreach($ecomtrackingNo as $track){
					 $name = 'Ecomexpress';
					 $number = $orderpdetail['awb'];
					 $TrackinglabelText['tracking']  = "<p>Shipment name: ".$name.", Number: ".$number."</p>";
					 $TrackinglabelText['shipment'] = $name;
				  //}
				}
			}
		}

		if(!empty($orderpdetail['shipsy_reference_numbers'])){
		    $shipsyTrackingname = 'DTDC';
		    $shipsyTrackingNo = $orderpdetail['shipsy_reference_numbers'];
		    $TrackinglabelText['tracking']  = "<p>Shipment name: ".$shipsyTrackingname.", Number: ".$shipsyTrackingNo."</p>";
			$TrackinglabelText['shipment'] = $shipsyTrackingname;
		}


		if (empty($TrackinglabelText)) {
			$TrackinglabelText['tracking']  = "";
			$TrackinglabelText['shipment'] = "Not Assigned";
		}
   
		    $endtime = microtime(true); // when function ends
            $duration  = $endtime - $starttime;
		    echo '<!--getMainTrackingInformation start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';
         return $TrackinglabelText;

    }

    public function getTrackingInformation($order){
        
		$starttime = microtime(true);
        $TrackinglabelText = [];
        $orderId = $order->getId();
        $deltrackingNo = $this->collectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('orderid',$orderId);
         if(!empty($deltrackingNo->getFirstItem()->getData('order_increment_id'))){
            $delhiveryTrackingNo = $deltrackingNo->getFirstItem()->getData('awb');
            if($delhiveryTrackingNo == ''){
              $TrackinglabelText['tracking'] = '<p>Delhivery Shipment</p><p style="color:red;">not created</p>';
            } else {
              foreach($deltrackingNo as $track){
                 $name = 'Delhivery';
                 $number = $track->getData('awb');
                 $TrackinglabelText['tracking']  = "<p>Shipment name: ".$name.", Number: ".$number."</p>";
                $TrackinglabelText['shipment'] = $name;
              }
            }
        }

        $ecomtrackingNo = $this->ecomcollectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('orderid',$orderId);
        if(!empty($ecomtrackingNo->getFirstItem()->getData('orderid'))){
            $ecomexpressTrackingNo = $ecomtrackingNo->getFirstItem()->getData('awb');
            if($ecomexpressTrackingNo == ''){
              $TrackinglabelText['tracking'] = '<p>Ecomexpress Shipment</p><p style="color:red;">not created</p>';
            } else {
              foreach($ecomtrackingNo as $track){
                 $name = 'Ecomexpress';
                 $number = $track->getData('awb');
                 $TrackinglabelText['tracking']  = "<p>Shipment name: ".$name.", Number: ".$number."</p>";
                $TrackinglabelText['shipment'] = $name;
              }
            }
        }

            if(!empty($order->getShipsyReferenceNumbers())){
            $shipsyTrackingname = 'DTDC';
            $shipsyTrackingNo = $order->getShipsyReferenceNumbers();
            $TrackinglabelText['tracking']  = "<p>Shipment name: ".$shipsyTrackingname.", Number: ".$shipsyTrackingNo."</p>";
                $TrackinglabelText['shipment'] = $shipsyTrackingname;
            }

           if (empty($TrackinglabelText)) {
                $TrackinglabelText['tracking']  = "";
                $TrackinglabelText['shipment'] = "Not Assigned";
           }
		   $endtime = microtime(true); // when function ends
           $duration  = $endtime - $starttime;
		   echo '<!--getTrackingInformation start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';
         return $TrackinglabelText;

    }

      public function getMainDriverInformation($orderdetail)
    {
        $starttime = microtime(true);
		$labelText = null; 
        if($orderdetail) {
            $order_status = $orderdetail['order_status']; 
            if($order_status == 'order_delivered') {
             $store_name = $orderdetail['store_name'];
             $pickup_person_name = $orderdetail['pickup_person_name'];
             $given_person = $orderdetail['given_person'];
              $labelText = "<p>Store name: ".$store_name.", Pickup Person name: ".$pickup_person_name.", Given Person:".$given_person."</p>";
            }
                $driverId = $orderdetail['driver_id'];
                $deliveryboyId =$orderdetail['deliveryboy_id'];
                if(!empty($driverId)){
                    if($orderdetail['driver_name']) {
                       $labelText = "<p>Driver Name: ".$orderdetail['driver_name'].", Driver Mobile: ".$orderdetail['driver_mobile'].", Auto Number: ".$orderdetail['auto_number']."</p>";
            
				    } elseif(!empty($orderdetail['tracking_title'])){
					   $name = $orderdetail['tracking_title'];
					   $number = $orderdetail['tracking_number'];
					   $labelText = "<p>Shipment name: ".$name.", Number: ".$number."</p>";
					}
                } elseif(!empty($deliveryboyId)) {
                    $deliveryboyName = $orderdetail['name'];
                    $deliveryboyContact = $orderdetail['mobile_number'];
                    $deliveryvehicleNumber = $orderdetail['vehicle_number'];
                    $labelText = "<p>Driver Name: ".$deliveryboyName.", Driver Mobile: ".$deliveryboyContact.", Auto Number: ".$deliveryvehicleNumber."</p>";
                }
        }

		$endtime = microtime(true); // when function ends
        $duration  = $endtime - $starttime;
		echo '<!--getMainDriverInformation start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';
        return $labelText;
    }

    public function getDriverInformation($orderId)
    {
        $starttime = microtime(true);
		$labelText = null;
        $pconnection = $this->resourceConnection->getConnection();
        $ptableName = $this->resourceConnection->getTableName('ci_stores_order');
        $psql = $pconnection->select()->from($ptableName)->where('order_id = ?', $orderId);
        $pickupresult = $pconnection->fetchRow($psql); 
          
        if($pickupresult) {
           $order_status = $pickupresult['order_status']; 
          if($order_status == 'order_delivered') {
             $store_name = $pickupresult['store_name'];
             $pickup_person_name = $pickupresult['pickup_person_name'];
             $given_person = $pickupresult['given_person'];
              $labelText = "<p>Store name: ".$store_name.", Pickup Person name: ".$pickup_person_name.", Given Person:".$given_person."</p>";
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
                       $labelText = "<p>Driver Name: ".$info->getData('driver_name').", Driver Mobile: ".$info->getData('driver_mobile').", Auto Number: ".$info->getData('auto_number')."</p>";
            
				    } elseif(!empty($drivers->getFirstItem()->getData('tracking_title'))){
					   $name = $drivers->getFirstItem()->getData('tracking_title');
					   $number = $drivers->getFirstItem()->getData('tracking_number');
					   $labelText = "<p>Shipment name: ".$name.", Number: ".$number."</p>";
					}
                } elseif(!empty($deliveryboyId)) {
                    $deliveryboy = $this->deliveryboy->create()->load($deliveryboyId);
                    $deliveryboyName = $deliveryboy->getName();
                    $deliveryboyContact = $deliveryboy->getMobileNumber();
                    $deliveryvehicleNumber = $deliveryboy->getVehicleNumber();
                    $labelText = "<p>Driver Name: ".$deliveryboyName.", Driver Mobile: ".$deliveryboyContact.", Auto Number: ".$deliveryvehicleNumber."</p>";
                }
            }
        }

		$endtime = microtime(true); // when function ends
        $duration  = $endtime - $starttime;
		echo '<!--getDriverInformation start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';
        return $labelText;
    }

    public function rmaTableData()
    {
        $starttime = microtime(true);
		if (!$this->returnsData) {
            $data = [];
            $highestTime = $this->scopeConfig->getValue('schooldashboard/ordertime/highest_orders_time');
            $date = (new \DateTime())->modify($highestTime)->format('Y-m-d h:i:s');
			$school_id = $this->getSchoolNameSessionValue();
			//$school_id = '1357';
            $returns = $this->returnsFactory->create()->getCollection();
			//returns->addFieldToFilter('created_at', ['gteq' => $date]);
			$salesorder_table_name = $this->resourceConnection->getTableName('sales_order'); 
            //$returns->getSelect()->joinLeft(array('so' => $salesorder_table_name),'main_table.order_id = so.entity_id');
			 $returns->getSelect()->joinLeft(
            ['so' => $salesorder_table_name],
            'main_table.order_id = so.entity_id',
            ['school_name', 'school_id']
            );
			$returns->addFieldToFilter('main_table.created_at', ['gteq' => $date]);
			$returns->addFieldToFilter('so.school_id',['in'=>$this->getSchoolNameSessionValue()]);
			//echo $returns->getSelect()->__toString();
            foreach ($returns as $value) {
                $data[] = ['status' => $value->getStatusLabel(), 'reason' => $value->getItemsCollection()->getFirstItem()->getReasonLabel(), 'increment_id' => $value->getIncrementId()];
            }
            $this->returnsData = $data;
        }
        $noOfStatus = array_unique(array_column($this->returnsData, 'status'));
        $noOfReason = array_unique(array_column($this->returnsData, 'reason'));
        if ($noOfStatus && $noOfReason) {
            $html = '<table border="1"><tbody><tr style="font-weight: 700;background-color: #e6e684;"><td rowspan="2">RMA Reason</td><td colspan="'.count($noOfStatus).'">RMA Status</td></tr><tr style="background-color: #e88484;">';
            foreach($noOfStatus as $status) {
                $html.= '<td style="font-weight: 700;">'.($status ?? "Not Assigned").'</td>';
            }
            $html.= '</tr>';
            foreach($noOfReason as $reason) {
                if ($reason) {
                    $html.= '<tr><td style="font-weight: 700;">'.$reason.'</td>';
                    foreach($noOfStatus as $status) {
                        $html.= '<td>'.$this->returnsFilter($status, $reason).'</td>';
                    }
                    $html.= '</tr>';
                }
            }
            $html.='</tbody></table>';

		    $endtime = microtime(true); // when function ends
            $duration  = $endtime - $starttime;
		    echo '<!--rmaTableData start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';

            return $html;
        }
    }

    private function returnsFilter($rmaStatus = null, $rmaReason = null) {
        $starttime = microtime(true);
		$final = $this->returnsData;
        if ($rmaStatus) {
            $final = array_filter($final, fn($value) => $value['status'] == $rmaStatus);
        }

        if ($rmaReason) {
            $final = array_filter($final, fn($value) => $value['reason'] == $rmaReason);
        }

		$endtime = microtime(true); // when function ends
        $duration  = $endtime - $starttime;
	    echo '<!--returnsFilter start='.$starttime . ' end='.$endtime. ' duration='.$duration.'-->';
        return count($final);
    }

    public function highOrdersTime()
    {
        return $this->scopeConfig->getValue('schooldashboard/ordertime/highest_orders_time');
    }

	public function getSchoolNameSessionValue()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->create('Magento\Customer\Model\Session');
		//$school_name = $_SESSION['school_name'];
		//$customerSession->setSchoolNameValue($school_name);
	    $school_name = $customerSession->getSchoolNameValue();
		return $school_name;
	}

}
