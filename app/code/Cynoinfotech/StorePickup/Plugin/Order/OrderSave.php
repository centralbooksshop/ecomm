<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Plugin\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Session\Storage;

class OrderSave
{
    protected $helper;
    protected $storepickuporderFactory;
    protected $objectManager = null;
    protected $sessionStorage;

    public function __construct(
        \Cynoinfotech\StorePickup\Helper\Data $helper,
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory,
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Storage $sessionStorage
    ) {
        $this->helper = $helper;
        $this->storepickuporder = $storepickuporderFactory;
        $this->storepickupFactory = $storepickupFactory;
        $this->objectManager = $objectManager;
        $this->sessionStorage = $sessionStorage;
    }

    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $data = $this->helper->getStorepickupDataFromSession();
		
        if(!empty($data['store_pickup'])){
		
			$orderIncrementId = $order->getIncrementId();
            $orderId = $order->getId();
            // $orderData = $order->getData();
			$store_pickup_id = $data['store_pickup'];
			
			if (is_array($data) && !($data === null)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $orderObj = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
                // $orderDetails = $orderObj->getData();

				$data['order_id'] = $orderId;
				
                $pickup_address = $this->getPickupAddress($store_pickup_id);
                $pickupStoreData = $this->getPickupAddressArray($store_pickup_id);
                $data['pickup_address'] = $pickup_address;
                $data['increment_id'] = $orderIncrementId;
                $data['store_name'] = $pickupStoreData['name'];
                $data['pickup_person_name'] = $data['pickup_person_name'];
                $data['pickup_person_id'] = $data['pickup_person_id'];
                $data['customer_phone'] = $orderObj->getShippingAddress()->getTelephone();
                $data['payment_method'] = $orderObj->getPayment()->getMethodInstance()->getTitle();
                $data['order_status'] = $orderObj->getStatus();
                $data['given_person'] = '';
                $data['delivery_date'] = '';
                $data['store_status'] = '';

				$obj = $this->objectManager->get('\Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder');
				$obj->SavePickupOrder($data);
				$this->sessionStorage->unsData($this->helper->getStorepickupAttributesSessionKey());
			}			
		}
		
		return $order;
    }
    
    public function getPickupAddress($id)
    {
        $storepickup = $this->storepickupFactory->create();
        $pickup_address = $storepickup->getCollection()->addFieldToFilter('entity_id', ['eq' => $id]);
        
        $full_address = '';
        if (!empty($pickup_address->getData())) {
            $full_address.= $pickup_address->getData()[0]['name'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_address'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_city'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_state'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_pincode'] . ', ';
            $full_address.= $pickup_address->getData()[0]['store_country'] . ', ';
            $full_address.= 'T: ' . $pickup_address->getData()[0]['store_phone'] . ', ';
            $full_address.= 'Email: ' . $pickup_address->getData()[0]['store_email'];
        }
         
        return $full_address;
    }

    public function getPickupAddressArray($id)
    {
        $storepickup = $this->storepickupFactory->create();
        $pickup_address = $storepickup->getCollection()->addFieldToFilter('entity_id', ['eq' => $id]);
        
        $full_address = [];
        if (!empty($pickup_address->getData())) {
            $full_address['name'] = $pickup_address->getData()[0]['name'];
            $full_address['store_address'] = $pickup_address->getData()[0]['store_address'];
            $full_address['store_city'] = $pickup_address->getData()[0]['store_city'];
            $full_address['store_state'] = $pickup_address->getData()[0]['store_state'];
            $full_address['store_pincode'] = $pickup_address->getData()[0]['store_pincode'];
            $full_address['store_country'] = $pickup_address->getData()[0]['store_country'];
            $full_address['store_phone']= $pickup_address->getData()[0]['store_phone'];
            $full_address['store_email']= $pickup_address->getData()[0]['store_email'];
        }
         
        return $full_address;
    }
}
