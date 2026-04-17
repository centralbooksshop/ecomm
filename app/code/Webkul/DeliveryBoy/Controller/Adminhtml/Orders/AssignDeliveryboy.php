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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Orders;

use Magento\Framework\Exception\LocalizedException;

class AssignDeliveryboy extends \Magento\Backend\App\Action
{
    /**
     * Current order otp
     *
     * @var string
     */
    protected $otp = null;

    /**
     * Order already assigned flag
     *
     * @var int
     */
    protected $alreadyAssignedTo = 0;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\DeliveryBoy\Model\OrderFactory
     */
    protected $deliveryboyOrderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $deliveryboyHelper;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection
     */
    protected $tokenResourceCollection;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Operation
     */
    private $operationHelper;
	protected $ProcessCBOOrdersFactory;
	protected $variable;

    /**
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Backend\App\Action\Context $context,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory,
		\Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory $ProcessCBOOrdersFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger,
		\Retailinsights\SmsOnOrderStatusChange\Helper\Data $helperData,
		\Magento\Variable\Model\Variable $variable,
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper
    ) {
        parent::__construct($context);

        $this->deliveryboy = $deliveryboy;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->orderFactory = $orderFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->jsonFactory = $jsonFactory;
        $this->deliveryboyOrderFactory = $deliveryboyOrderFactory;
		$this->ProcessCBOOrdersFactory = $ProcessCBOOrdersFactory;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->tokenResourceCollection = $tokenResourceCollection;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
		$this->helperData = $helperData;
		$this->variable = $variable;
        $this->operationHelper = $operationHelper;
    }

    /**
     * Assign Order To deliveryboy.
     *
     * @return \Magetno\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
		//$wholeData = $this->getRequest()->getParams();
        //$incrementId = $wholeData["incrementId"] ?? 0;
        //$deliveryboyId = $wholeData["deliveryboyId"] ?? 0;

		$incrementIds = $this->getRequest()->getPost('orderIds');
		//$driverId = $this->getRequest()->getPost('driverId');
		$deliveryboyId = $this->getRequest()->getPost('driverId') ?? 0;

		foreach($incrementIds as $key => $incrementIdsvalue){
		$message[$incrementIdsvalue] = $incrementIdsvalue;
		$incrementId = $incrementIdsvalue ?? 0;

        $resultJsonFactory = $this->jsonFactory;
        $result = $resultJsonFactory->create();
        if ($deliveryboyId) {
            $deliveryBoy = $this->deliveryboy->load($deliveryboyId);
            if ($deliveryBoy->getData("availability_status") == 0) {
                return $result->setData($this->getFailureResultArray(
                    (string)__("Deliveryboy is not available.")
                ));
            }
        }
        $orderFactory = $this->orderFactory;
        $order = $orderFactory->create()->loadByIncrementId($incrementId);

       
            if (!$this->deliveryboyHelper->canAssignOrder($order)) {
                throw new LocalizedException(__(
                    'Unable to perform the requested operation. The order is in %1 state.',
                    $order->getState()
                ));
            }

			    if ($deliveryboyId) {
				$deliveryboyOrderColl = $this->collectionFactory->create()
				->addFieldToFilter("deliveryboy_id", $deliveryboyId)
				->addFieldToFilter("order_status", 'dispatched_to_courier');
				$deliverboyordercoll = count($deliveryboyOrderColl);
				$deliveryBoy = $this->deliveryboy->load($deliveryboyId);
				$deliveryboyorder_limit = $deliveryBoy->getData("order_limit");
				if(!empty($deliveryboyorder_limit)) {
				 $totalorder = count($incrementIds);
				 $order_limit_diff = $deliveryboyorder_limit - $deliverboyordercoll;
				 $order_limit_msg = 'Deliveryboy Order limit exceeded';
					if ($deliveryboyorder_limit <= $deliverboyordercoll) {
						return $result->setData($this->getFailureResultArray((string)__($order_limit_msg)));
					}
				}
				
			   }
				
				
				// if(trim($value) == 'Assign Couriers'){
					//$result[$value] = $this->saveOrderDriver(trim($value), trim($deliveryboyId));
				// }
				 // get order id from increment id
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementIdsvalue);

				$orderId = $orderInfo->getId();
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);

				$i = 0;
				$pin = "";
				while ($i < 4) {
					$pin .= random_int(0, 9);
					$i++;
				}
				$delh_otp = $pin;

				if($order->getId()){
					// save orders with drivers
					$model = $this->ProcessCBOOrdersFactory->create();

					if($orderId != '' && $deliveryboyId != '' ){
					$model->addData([
						"order_id" => $orderId,
						"deliveryboy_id" => $deliveryboyId,
						"tracking_title" =>'',
						"tracking_number" =>''
						]);
					
						$saveData = $model->save();
					}
					if($saveData){
						$state = $order->getState();
						$status = 'dispatched_to_courier';
						$comment = 'order status is dispatched_to_courier';
						$isNotified = false;
						$order->setState($state);
						$order->setStatus($status);
						$order->addStatusToHistory($order->getStatus(), $comment);
						$order->save(); 
						$this->SendMessageAssignDriver($delh_otp,$deliveryboyId, $order);
					} 
				} 
			
            //ravi custom code end
            $assignedOrder = $this->verifyUsernData($incrementId);
            $assignedId = 0;
            if ($assignedOrder->getId() > 0) {
                $assignedId = $assignedOrder->getId();
                $this->alreadyAssignedTo = $assignedOrder->getDeliveryboyId();
            }
            $deliveryboyOrder = $this->deliveryboyOrderFactory->create();
            if ($assignedId != 0) {
                $deliveryboyOrder->setId($assignedId);
            }

            $deliveryboyOrder->setOtp($delh_otp)
                ->setAssignStatus("")
                ->setOrderId($order->getId())
                //->setOrderStatus($order->getState())
			    ->setOrderStatus('dispatched_to_courier')
                ->setDeliveryboyId($deliveryboyId)
                ->setIncrementId($order->getIncrementId())
                ->save();
            $this->sendEmail($delh_otp,$deliveryboyId, $order);
            $this->sendAssignmentNotification($deliveryboyId, $order);
            if ($this->alreadyAssignedTo != 0) {
                $this->sendUnAssignmentNotification($deliveryboyId, $order);
            }
		}
           
			return $result->setData($this->getSuccessResultArray($message));
        } catch (\Exception $e) {
            return $result->setData($this->getFailureResultArray((string)__($e->getMessage())));
        }
    }


	  public function SendMessageAssignDriver($delh_otp,$deliveryboyId, $order): void
	   {
        
		$deliveryboy = $this->deliveryboy->load($deliveryboyId);
        $deliveryboyName = $deliveryboy->getName();
        $deliveryboyContact = $deliveryboy->getMobileNumber();
		$incrementId = $order->getIncrementId();
        $custmerName = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();   
        //$custmerName = $order->getShippingAddress()->getData('firstname');
		$helpdesklinkvalue = $this->variable->loadByCode('sms-helpdesklinkvalue', 'admin');
		$helpdesklink = $helpdesklinkvalue->getPlainValue();
		if(empty($accountlink)) {
          $accountlink ='';
		}
		if(empty($helpdesklink)) {
          $helpdesklink ='';
		}
        
        $msg = "Dear ".$custmerName.", Your order ".$incrementId." has been successfully assigned to the Delivery Boy ".$deliveryboyName.". You will receive the order soon, your OTP for delivery is ".$delh_otp.". Reach us at ".$helpdesklink." for any assistance - centralbooksonline.com.";
        
        $mobile = $order->getShippingAddress()->getTelephone();
        
        $sms = $this->helperData->AppSendSms($msg,"Y",$mobile);
         
     }

    /**
     * Return error message.
     *
     * @param string $message
     * @return array
     */
    public function getFailureResultArray($message)
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }

    /**
     * Return success message.
     *
     * @param string $message
     * @return array
     */
    public function getSuccessResultArray($message)
    {
        return [
            'success' => true,
            'message' => $message
        ];
    }

    /**
     * Verify Deliveryboy order.
     *
     * @param int $incrementId
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function verifyUsernData($incrementId)
    {
        $deliveryboyOrderCollection = $this->collectionFactory->create()
            ->addFieldToFilter("increment_id", $incrementId);
        $this->_eventManager->dispatch(
            'wk_deliveryboy_assigned_order_collection_apply_filter_event',
            [
                'deliveryboy_order_collection' => $deliveryboyOrderCollection,
                'collection_table_name' => 'main_table',
                'owner_id' => 0,
            ]
        );
        $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();

        return $deliveryboyOrder;
    }

    /**
     * Generate Random Otp.
     *
     * @return string
     */
    public function getOtp(): string
    {
        if (!$this->otp) {
            $i = 0;
            $pin = "";
            while ($i < 4) {
                $pin .= random_int(0, 9);
                $i++;
            }
            $this->otp = $pin;
        }
        return $this->otp;
    }

    /**
     * Send Email to Customer.
     *
     * @param mixed $deliveryboyId
     * @param mixed $order
     * @return void
     */
    public function sendEmail($delh_otp,$deliveryboyId, $order): void
    {
        try {
            $deliveryboy = $this->deliveryboy->load($deliveryboyId);
            $deliveryboyName = $deliveryboy->getName();
            $templateVariables = [];
            $templateVariables["otp"] = $delh_otp;
            $templateVariables["orderDate"] = $this->deliveryboyHelper->formatDateTimeCurrentLocale(
                $order->getCreatedAt()
            );
            $templateVariables["orderStatus"] = $order->getStatus();
            $templateVariables["customerName"] = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();
            $templateVariables["deliveryboyName"] = $deliveryboyName;
            $templateVariables["orderIncrementId"] = $order->getIncrementId();
            $templateVariables["deliveryboyContact"] = $deliveryboy->getMobileNumber();
            $this->inlineTranslation->suspend();
            $senderInfo = [
                "name"  => "Admin",
                "email" => $this->helper->getGeneralEmail()
            ];
            $receiverInfo = [
                "name"  => $templateVariables["customerName"],
                "email" => $order->getCustomerEmail()
            ];
            $template = "deliveryboy_email_otp";
            $template = $this->transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        "area"  => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo["email"], $receiverInfo["name"]);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Common Method for Sending FCM Notification.
     *
     * @param int $deliveryboyId
     * @param DeliveryboyOrder $order
     * @return void
     */
    public function sendAssignmentNotification($deliveryboyId, $order): void
    {
        $message = [
            "id" => $order->getId(),
            "body" => __("Your have received new order to deliver."),
            "title" => __("New Order Assigned."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Your have received new order to deliver."),
            "incrementId" => $order->getIncrementId(),
            "notificationType" => "deliveryBoyNewOrder"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $tokenCollection = $this->tokenResourceCollection
            ->addFieldToFilter("deliveryboy_id", $deliveryboyId);
        foreach ($tokenCollection as $eachToken) {
            $fields['to'] = $eachToken->getToken();
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }
            $result = $this->operationHelper->send($headers, $fields);
            if (isset($result["success"], $result["failure"])) {
                if ($result["success"] == 0 && $result["failure"] == 1) {
                    $eachToken->delete();
                }
            }
        }
    }

    /**
     * Send UnAsignmentNotification
     *
     * @param int $deliveryboyId
     * @param DeliveryboyOrder $order
     * @return void
     */
    public function sendUnAssignmentNotification($deliveryboyId, $order): void
    {
        $message = [
            "id" => $order->getId(),
            "body" => __("One order is unassigned form you."),
            "title" => __("Order UnAssigned."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("One order is unassigned form you."),
            "incrementId" => $order->getIncrementId(),
            "notificationType" => "orderUnassigned"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $tokenCollection = $this->tokenResourceCollection
            ->addFieldToFilter("deliveryboy_id", $this->alreadyAssignedTo);
        foreach ($tokenCollection as $eachToken) {
            $fields["to"] = $eachToken->getToken();
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }
            $result = $this->operationHelper->send($headers, $fields);
            if (isset($result["success"], $result["failure"])) {
                if ($result["success"] == 0 && $result["failure"] == 1) {
                    $eachToken->delete();
                }
            }
        }
    }
}
