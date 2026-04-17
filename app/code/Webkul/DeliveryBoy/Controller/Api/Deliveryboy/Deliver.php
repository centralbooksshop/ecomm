<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Api\Deliveryboy;

use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Model\Deliveryboy as Deliveryboy;

class Deliver extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * Deliver Order to customer.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
		$this->verifyRequest();

            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->otp == 0 || !is_numeric($this->otp)) {
                throw new LocalizedException(__("Invalid OTP."));
            }
            if ($this->incrementId == "") {
                throw new LocalizedException(__("Invalid Order."));
            }
            if ($this->deliveryboyId == 0 || !is_numeric($this->deliveryboyId)) {
                throw new LocalizedException(__("Invalid Deliveryboy."));
            }
            $this->deliveryBoy = $this->deliveryboyResourceCollection->create()
                ->addFieldToSelect("id")
                ->addFieldToSelect("status")
                ->addFieldToSelect("name")
                ->addFieldToSelect("availability_status")
                ->addFieldToFilter("id", $this->deliveryboyId)
                ->getFirstItem();
            
            if (!$this->isDeliveryboyAvailable()) {
                throw new LocalizedException(__("Deliveryboy unavailable."));
            }
            $deliveryboyOrderCollection = $this->deliveryboyOrderResourceCollection->create()
                ->addFieldToFilter("increment_id", $this->incrementId)
                ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
            $this->_eventManager->dispatch(
                'wk_deliveryboy_assigned_order_collection_apply_filter_event',
                [
                    'deliveryboy_order_collection' => $deliveryboyOrderCollection
                ]
            );
            $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();
            
            if ($deliveryboyOrder->getDeliveryboyId() == $this->deliveryboyId &&
                $deliveryboyOrder->getOtp() == $this->otp
            ) {
                $order = $this->orderFactory->create()->load($deliveryboyOrder->getOrderId());
                if (!$this->deliveryboyHelper->canAssignOrder($order)) {
                    throw new LocalizedException(
                        __(
                            'Unable to perform the requested operation. The order is in %1 state.',
                            $order->getState()
                        )
                    );
                }
                if ($order->getState() == Order::STATE_NEW || $order->getState() == Order::STATE_PROCESSING ||
                    $order->getState() == Order::STATE_PENDING_PAYMENT || $order->getState() == 'complete'
		) {

	
		   if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
			   $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			                  
    			   $uploader = $objectManager->create(\Magento\MediaStorage\Model\File\Uploader::class, ['fileId' => 'image']);
    			   $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']); // Specify allowed file extensions
			   $uploader->setAllowRenameFiles(true);
			   $mediaPath = $this->mediaDirectory->getAbsolutePath('POD');
			   
    			   if (!is_dir($mediaPath)) {
        			try {
            				mkdir($mediaPath, 0777, true);  
        		        } catch (\Exception $e) {
            				throw new \Magento\Framework\Exception\LocalizedException(__('Unable to create folder: ' . $e->getMessage()));
        			}
    			    }

    			  $path = $mediaPath . '/images'; 
    			  if (!is_dir($path)) {
        			  try {
            				mkdir($path, 0777, true);
        			} catch (\Exception $e) {
            				throw new \Magento\Framework\Exception\LocalizedException(__('Unable to create images folder: ' . $e->getMessage()));
        			}
			  }

    			  try {
				  $result = $uploader->save($path); 
				  $imageUrl = 'POD/images/' . $result['file']; 
				  $deliveryboyOrder->setPodImagePath($imageUrl);
				  
    			} catch (\Exception $e) {
     			   throw new \Magento\Framework\Exception\LocalizedException(__('Image upload failed: ' . $e->getMessage()));
   			 }
		   }
		   
                    if (!$order->canInvoice()) {
                        $order->addStatusHistoryComment(__("Order cannot be invoiced."), false);
                        $order->save();
                    }

					    $state = $order->getState();
						$status = 'order_delivered';
						$comment = '';
						$order->setState($state);
						$order->setStatus($status);
						$order->addStatusToHistory($order->getStatus(), $comment);
						$order->save(); 
                    //$this->processInvoice($order);
                    //$this->processShipment($order);
                    //$mageOrderState = $order->getState();
					$mageOrderState = 'order_delivered';
					
                    $this->setDeliveryboyOrderStatus($deliveryboyOrder, $mageOrderState);
                    if ($this->alternateDelivery) {
                        $deliveryboyOrder->setAlternateDelivery($this->alternateDelivery);
                    }
                    $deliveryboyOrder->save();
                    $this->_eventManager->dispatch(
                        'deliveryboy_order_delivered_event',
                        [
                            'deliveryboy_order' => $deliveryboyOrder,
                            'amount' => $this->amount,
                        ]
                    );
                    
                    $this->sendNotificationToAdmin($order);
                    $this->sendNotificationToCustomer($order);
                    $this->sendNotificationToDeliveryboy($order);
                    $this->deliveryboyComment->create()
                        ->setComment(__("Order Delivered Successfully."))
                        ->setSenderId($deliveryboyOrder->getDeliveryboyId())
                        ->setIsDeliveryboy(1)
                        ->setOrderIncrementId($deliveryboyOrder->getIncrementId())
                        ->setDeliveryboyOrderId($deliveryboyOrder->getId())
                        ->setCommentedBy($this->deliveryBoy->getName())
                        ->setCreatedAt($this->date->gmtDate())
                        ->save();
                }
                if ($this->alternateDelivery) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $helper = $objectManager->get('Retailinsights\Registers\Helper\Data');
                    $helper->SendSms("Use code", "to login for centralbooksonline.", "Y", "", $order->getShippingAddress()->getTelephone());
                }
            } else {
                throw new LocalizedException(__("Invalid OTP."));
            }
            $this->returnArray["message"] = __("Order Delivered Successfully.");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Generate Invoice.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function processInvoice($order)
    {
        if ($order->canInvoice() && !$order->getPayment()->canCapture()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transaction = $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transaction->save();
            $this->invoiceSender->send($invoice);
            $order->addStatusHistoryComment(__("Notified customer about invoice #%1.", $invoice->getId()))
                ->setIsCustomerNotified(true)
                ->save();
        } else {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
        }
    }

    /**
     * Generate Shipment.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function processShipment(\Magento\Sales\Model\Order $order)
    {
        if ($order->canShip()) {
            $shipment = $this->orderConverter->toShipment($order);
            foreach ($order->getAllItems() as $orderItem) {
                // Check if order item has qty to ship or is virtual
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyToShip();
                // Create shipment item with qty ////////////////////
                $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                // Add shipment item to shipment ////////////////////
                $shipment->addItem($shipmentItem);
            }
            // Register shipment ////////////////////////////////////
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            // Save created shipment and order //////////////////////
            $shipment->save();
            $shipment->getOrder()->save();
            // Send email ///////////////////////////////////////////
            $this->shipmentNotifier->notify($shipment);
            $shipment->save();
			$orderId = $order->getId();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$mainorder = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                $mainorder->setData('shipment_type','cboshipping'); 
                $mainorder->save();
        }
    }

    /**
     * Send Notification to Admin.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function sendNotificationToAdmin(
        \Magento\Sales\Model\Order $order
    ): void {
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $message = [
            "id" => time(),
            "body" => __("Order Delivered #%1", $this->incrementId),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Order Delivered #%1", $this->incrementId),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $this->tokenResourceCollection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $tokenCollection = $this->tokenResourceCollection->getNewEmptyItem()->getCollection();
        $tokenCollection = $tokenCollection
            ->addFieldToFilter("is_admin", 1);
        foreach ($tokenCollection as $eachToken) {
            $fields["to"] = $eachToken->getToken();
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }
            $result = $this->operationHelper->send($headers, $fields);
            if (count($result) !== 0) {
                if ($result["success"] == 0 && $result["failure"] == 1) {
                    $eachToken->delete();
                }
            }
        }
    }

    /**
     * Send Notification to customer.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function sendNotificationToCustomer(\Magento\Sales\Model\Order $order): void
    {
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $message = [
            "id" => time(),
            "body" => __("Order Delivered #%1", $this->incrementId),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Order Delivered #%1", $this->incrementId),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $this->tokenResourceCollection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $tokenCollection = $this->tokenResourceCollection->getNewEmptyItem()->getCollection();
        $tokenCollection = $tokenCollection
            ->addFieldToFilter("deliveryboy_id", $order->getCustomerId());
        foreach ($tokenCollection as $eachToken) {
            $fields["to"] = $eachToken->getToken();
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }
            $result = $this->operationHelper->send($headers, $fields);
            if (count($result) !== 0) {
                if ($result["success"] == 0 && $result["failure"] == 1) {
                    $eachToken->delete();
                }
            }
        }
    }

    /**
     * Send Notification To deliveryboy.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function sendNotificationToDeliveryboy(\Magento\Sales\Model\Order $order): void
    {
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $message = [
            "id" => time(),
            "body" => __("Order Delivered #%1", $this->incrementId),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Order Delivered #%1", $this->incrementId),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $this->tokenResourceCollection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $tokenCollection = $this->tokenResourceCollection->getNewEmptyItem()->getCollection();
        $tokenCollection = $tokenCollection
            ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
        foreach ($tokenCollection as $eachToken) {
            $fields["to"] = $eachToken->getToken();
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }
            $result = $this->operationHelper->send($headers, $fields);
            if (count($result) !== 0) {
                if ($result["success"] == 0 && $result["failure"] == 1) {
                    $eachToken->delete();
                }
            }
        }
    }

    /**
     * Verify request.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {

        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->otp = trim($this->wholeData["otp"] ?? 0);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? 0);
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
            $this->amount = trim($this->wholeData["amount"] ?? 0);
            $this->alternateDelivery = trim($this->wholeData["alternateDelivery"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
        if ($this->otp == 0 || !is_numeric($this->otp)) {
            throw new LocalizedException(__("Invalid OTP."));
        }
        if ($this->incrementId == "") {
            throw new LocalizedException(__("Invalid Order."));
        }
        if (!($this->deliveryboyId > 0 &&
             $this->deliveryboy->load($this->deliveryboyId)->getId() == $this->deliveryboyId)
        ) {
            throw new LocalizedException(__("Invalid delivery boy."));
        }
    }

    /**
     * Is Deliveryboy available.
     *
     * @return bool
     */
    public function isDeliveryboyAvailable()
    {
        if (!($this->deliveryBoy->getStatus() == Deliveryboy::STATUS_ENABLED) ||
        !($this->deliveryBoy->getAvailabilityStatus() == Deliveryboy::STATUS_ENABLED)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Set deliveryboy order status.
     *
     * @param \Webkul\DeliveryBoy\Model\Order $deliveryboyOrder
     * @param string $status
     * @return void
     */
    protected function setDeliveryboyOrderStatus(
        \Webkul\DeliveryBoy\Model\Order $deliveryboyOrder,
        string $status
    ) {
         $deliveryboyOrder->setOrderStatus($status);
    }
}
