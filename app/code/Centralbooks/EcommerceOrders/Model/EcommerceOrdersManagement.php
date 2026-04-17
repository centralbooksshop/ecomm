<?php
declare(strict_types=1);

namespace Centralbooks\EcommerceOrders\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class EcommerceOrdersManagement implements \Centralbooks\EcommerceOrders\Api\EcommerceOrdersManagementInterface
{
    protected $collectionFactory;
    protected $timezoneInterface;

     /**
     * Constructor
     *
     * @param TimezoneInterface $timezoneInterface
	 * @param CollectionFactory $collectionFactory
     */

	
    public function __construct(
        CollectionFactory $collectionFactory,
		TimezoneInterface $timezoneInterface
    ) {
        $this->collectionFactory = $collectionFactory;
		$this->timezoneInterface = $timezoneInterface;
    }
    /**
     * {@inheritdoc}
     */
    public function getEcommerceOrders($pagesize)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$objData = $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        //$current_date = $this->timezoneInterface->date()->format('Y-m-d');
		//$prev_date = $objData->date('Y-m-d', strtotime($current_date." -10 days"));
	    $orderCollection = $this->collectionFactory->create();
		$orderCollection->addAttributeToSelect('*');
	    //$orderCollection->addAttributeToFilter('main_table.status', ['in' => 'dispatched_to_courier']);
		//$orderCollection->addAttributeToFilter('main_table.status', ['in' => 'order_delivered']);
		  //->addAttributeToFilter('source_code', ['in' => $sourcecode])
		  //->addAttributeToFilter('created_at', ['gteq'=>$prev_date.' 00:00:00'])
	      //->addAttributeToFilter('created_at', ['lteq'=>$current_date.' 23:59:59']);
		if(!empty($pagesize)) {
		   $orderCollection->setPageSize($pagesize);
		}
		$orderCollection->addAttributeToSort('entity_id', 'desc');
		$orderCollection->getSelect()->
			joinLeft(
			["sop" => "sales_order_payment"],
			'main_table.entity_id = sop.parent_id',
			array('sop.method')
		  );
		$orderCollection->getSelect()->
			joinLeft(
			["sr" => "schools_registered"],
			'main_table.school_id = sr.school_name',
			array('sr.location_code','sr.add_schoolhub')
		);
		$orderCollection->getSelect()->
			joinLeft(
			["sh" => "centralbooks_schoolhub_schoolhub"],
			'sr.add_schoolhub = sh.schoolhub_id',
			array('sh.schoolhub_name')
		);
		$orderCollection->getSelect()->
			joinLeft(
			["cas" => "cbo_assign_shippment"],
			'main_table.entity_id = cas.order_id',
			array('cas.driver_id','cas.deliveryboy_id','cas.tracking_title','cas.tracking_number','dispatched_on' => 'cas.created_at')
		);
		/*$where = $orderCollection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
			//echo "<pre>";print_r($where);

	    foreach ($where as $key => $cond) {
			$condnew = str_replace('`dispatched_on`', 'cas.created_at', $cond);
			$where[$key] = str_replace('`created_at`', 'main_table.created_at', $condnew);
		}*/
		$orderCollection->getSelect()->
			joinLeft(
			["db" => "deliveryboy_deliveryboy"],
			'cas.deliveryboy_id = db.id',
			array('db.name','db.mobile_number','db.vehicle_number')
		);

		$orderCollection->getSelect()->
			joinLeft(
			["ca" => "cboshipping_autodrivers"],
			'cas.driver_id = ca.id',
			array('ca.driver_name','ca.driver_mobile','ca.auto_number')
		);
		$orderCollection->getSelect()->distinct(true);
		//->where('sr.school_code','sr.add_schoolhub');
        //echo $orderCollection->getSelect()->__toString();
		/*
        $orderCollection->setOrder(
			'created_at',
			'desc'
		);*/

		$totalorders = $orderCollection->getTotalCount();
		//echo '<pre>';print_r($orderCollection->getData());die;
		if($orderCollection->getTotalCount() > 0) 
		{   
			$orders = array();
			foreach ($orderCollection as $key => $order) {
				//echo '<pre>';print_r($order->getPayment()->getData());
				//echo '<pre>';print_r($order->getBillingAddress()->getData());
				//echo '<pre>';print_r($order->getShippingAddress()->getData());
				//echo '<pre>';print_r($order->getData());
				$items = [];
				foreach ($order->getItems() as $key => $item) {
					//echo '<pre>';print_r($item->debug());die;
					if($item['product_type'] == 'bundle') {
						   $bundle_name = $item->getName();
						   $bundle_sku  =  $item->getSku();
					} 
					
					if($item['product_type'] != 'bundle') {
					    $singleItem = array(
						  "name" => $item->getName(),
						  "sku" => $item->getSku(),
						  "quantity" => $item->getQtyOrdered(),
						  "price" => $item->getPrice()
						);
						array_push($items, $singleItem);
					}
				}
				
				$customer_name = $order->getCustomerFirstname().' '. $order->getCustomerLastname();
				$telephone = $order->getShippingAddress()->getData('telephone');
				$postcode = $order->getShippingAddress()->getData('postcode');
				$store_name =str_replace("\n"," ",$order->getStoreName());
                
                $driverId = $order['driver_id'];
                $deliveryboyId = $order['deliveryboy_id'];
                if(!empty($driverId)){
                        $trackingnumber = $order['driver_name']." : ".$order['driver_mobile']."  Auto:".$order['auto_number'];
                } else if(!empty($order['tracking_title'])) {
                    if($order['tracking_title'] == 'Clickpost') {
                        $tracking_number = $order['tracking_number'];
						$courier_name = $order['clickpost_courier_name'];
					    $trackingnumber = $courier_name." : ".$tracking_number;
					} else {
                        $name = $order['tracking_title'];
						$number = $order['tracking_number'];
						$trackingnumber = $name." : ".$number;
					}
                } else if(!empty($deliveryboyId)) {
                    $deliveryboyName = $order->getName();
					$deliveryboyContact = $order->getMobileNumber();
					$deliveryvehicleNumber = $order->getVehicleNumber();
					$trackingnumber = $deliveryboyName.":".$deliveryboyContact." Auto:".$deliveryvehicleNumber;
				}
				if(empty($trackingnumber)) {
					$trackingnumber = '';
				}
				if(!empty($order->getCreatedAt())) {
                   $created_at = $this->timezoneInterface->date(new \DateTime($order->getCreatedAt()))->format('Y-m-d H:i:s');
				} else {
                    $created_at = '';
				}
				if(!empty($order->getDispatchedOn())) {
                   $dispatched_on = $this->timezoneInterface->date(new \DateTime($order->getDispatchedOn()))->format('Y-m-d H:i:s');
				} else {
                    $dispatched_on = '';
				}
				
				if($order->getStatus() == 'order_delivered') {
				  $updated_at = $this->timezoneInterface->date(new \DateTime($order->getUpdatedAt()))->format('Y-m-d H:i:s');
				} else {
					 $updated_at = '';
				}

				if($order['store_id'] == 3) 
				{
					$orderdetails = [
					"Total Orders" => $totalorders,
					"ID" => $order->getIncrementId(),
					"Order Id" => $order->getEntityId(), 
					"Split Parent Order Id" => $order->getParentSplitOrder(), 
					"Purchase Point" => $store_name,
					"Purchase Date" => $created_at,
					"Bill-to Name" => $customer_name, 
					"Ship-to Name" => $customer_name, 
					"Grand Total (Base)" => floatval($order->getGrandTotal()), 
					"Grand Total (Purchased)" => floatval($order->getGrandTotal()),
					"Status" => $order->getStatus(), 
					"Billing Address" => $order->getBillingaddress()->getData(),
					"Shipping Address" => $order->getShippingAddress()->getData(),
					"Subtotal" => floatval($order->getSubtotal()), 
					"Shipping and Handling" => floatval($order->getPayment()->getData('shipping_amount')),
					"Total Refunded" => $order->getPayment()->getData('amount_refunded'),
					"Shipping Information" =>$order['shipping_description'],
					"Customer Name" => $customer_name,
					"Customer Email" => $order->getCustomerEmail(),
					"Payment Method" => $order->getMethod(),
					"Allocated sources" => 'Default Source',
					"Roll Number" => $order->getRollNo(),
					"Customer Phone Number" => $telephone,
					"School Name" => $order->getSchoolName(), 
					"School Code" => $order->getSchoolCode(), 
					"School Hub" => $order['schoolhub_name'],
					"Location Code" => $order['location_code'],
					"Student Name" => $order['student_name'],
					"Product Purchased" => $bundle_name,
					"SKU" => $bundle_sku, 
					//"Items" => $items, 
					"Back Order Status" => $order['is_backeordered_items'], 
					"Postcode" => $postcode, 
					"Reference Number" => $order['shipsy_reference_numbers'], 
					"Tracking URL" => $order['shipsy_tracking_url'],
					"Tracking Numbers" => $trackingnumber,
					"Dispatched Date" => $dispatched_on,
					"Delivered Date" => $updated_at,
					];
			    } else if($order['store_id'] == 1) {
				    $orderdetails = [
					"Total Orders" => $totalorders,
					"ID" => $order->getIncrementId(),
					"Order Id" => $order->getEntityId(), 
					"Split Parent Order Id" => $order->getParentSplitOrder(), 
					"Purchase Point" => $store_name,
					"Purchase Date" => $created_at,
					"Bill-to Name" => $customer_name, 
					"Ship-to Name" => $customer_name, 
					"Grand Total (Base)" => floatval($order->getGrandTotal()), 
					"Grand Total (Purchased)" => floatval($order->getGrandTotal()),
					"Status" => $order->getStatus(), 
					"Billing Address" => $order->getBillingaddress()->getData(),
					"Shipping Address" => $order->getShippingAddress()->getData(),
					"Subtotal" => floatval($order->getSubtotal()), 
					"Shipping and Handling" => floatval($order->getPayment()->getData('shipping_amount')),
					"Total Refunded" => $order->getPayment()->getData('amount_refunded'),
					"Shipping Information" =>$order['shipping_description'],
					"Customer Name" => $customer_name,
					"Customer Email" => $order->getCustomerEmail(),
					"Payment Method" => $order->getMethod(),
					"Allocated sources" => 'Default Source',
					"Roll Number" => $order->getRollNo(),
					"Customer Phone Number" => $telephone,
					"School Name" => $order->getSchoolName(), 
					"School Code" => $order->getSchoolCode(), 
					"School Hub" => $order['schoolhub_name'],
					"Location Code" => $order['location_code'],
					"Student Name" => $order['student_name'],
					//"Product Purchased" => $bundle_name,
					//"SKU" => $bundle_sku, 
					"Items" => $items, 
					"Back Order Status" => $order['is_backeordered_items'], 
					"Postcode" => $postcode, 
					"Reference Number" => $order['shipsy_reference_numbers'], 
					"Tracking URL" => $order['shipsy_tracking_url'],
					"Tracking Numbers" => $trackingnumber,
					"Dispatched Date" => $dispatched_on,
					"Delivered Date" => $updated_at,
					];
				}
				
				array_push($orders, $orderdetails);
			}
		}
		return $orders;

    }
}

