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
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\Exception\LocalizedException;

class AssignOrder extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * Current order otp
     *
     * @var string
     */
    protected $otp = null;

    /**
     * @var int
     */
    protected $alreadyAssignedTo = 0;

    /**
     * Assign Order.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $assignedOrder = $this->verifyUsernData();

			//ravi custom code start
			$state = $this->order->getState();
			$status = 'dispatched_to_courier';
			$comment = 'order status is dispatched_to_courier';
			$this->order->setState($state);
			$this->order->setStatus($status);
			$this->order->addStatusToHistory($this->order->getStatus(), $comment);
			$this->order->save(); 
            //ravi custom code end
            $assignedId = 0;
            if ($assignedOrder->getId() > 0) {
                $assignedId = $assignedOrder->getId();
                $this->alreadyAssignedTo = $assignedOrder->getDeliveryboyId();
            }
            $deliveryboyOrder = $this->deliveryboyOrder->create();
            if ($assignedId != 0) {
                $deliveryboyOrder->setId($assignedId);
            }
            $deliveryboyOrder
                ->setOtp($this->getOtp())
                ->setAssignStatus("")
                ->setOrderId($this->order->getId())
                //->setOrderStatus($this->order->getState())
				->setOrderStatus('dispatched_to_courier')
                ->setDeliveryboyId($this->deliveryboyId)
                ->setIncrementId($this->order->getIncrementId())
                ->save();

            $this->sendAssignmentNotification();
            if ($this->alreadyAssignedTo != 0) {
                $this->sendUnAssignmentNotification();
            }
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = (string)__("Order assigned successfully.");
            $this->sendEmail();
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = (string)__($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify User Data.
     *
     * @return \Webkul\DeliveryBoy\Api\Data\OrderInterface
     * @throws LocalizedException
     */
    protected function verifyUsernData(): \Webkul\DeliveryBoy\Api\Data\OrderInterface
    {
        if ($this->adminCustomerEmail !== $this->deliveryboyHelper->getAdminEmail()) {
            throw new LocalizedException(__("Unauthorized access."));
        }
        $this->order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
        if (!$this->order->getId()) {
            throw new LocalizedException(__("Invalid Order."));
        }
        if (!$this->deliveryboyHelper->canAssignOrder($this->order)) {
            throw new LocalizedException(
                __(
                    'Unable to perform the requested operation. The order is in %1 state.',
                    $this->order->getState()
                )
            );
        }
        if ($this->deliveryboyId == 0 || 0 == (int)$this->deliveryboyId) {
            throw new LocalizedException(__("Invalid Deliveryboy."));
        } else {
            $deliveryboy = $this->deliveryboy->load($this->deliveryboyId);
            if (!$deliveryboy->getAvailabilityStatus()) {
                throw new LocalizedException(__("Deliveryboy is offline."));
            }
        }
        $allowedShipping = explode(",", $this->deliveryboyHelper->getAllowedShipping());
        if (!in_array($this->order->getShippingMethod(), $allowedShipping)) {
            throw new LocalizedException(__("Sorry this order is not eligible for express delivery."));
        }
        return $this->deliveryboyOrderResourceCollection
            ->create()
            ->addFieldToFilter("increment_id", $this->incrementId)
            ->getFirstItem();
    }

    /**
     * Verify Request Data.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? "");
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * Function generates the 4 digit otp
     *
     * @return string
     */
    public function getOtp()
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
     * Send Email to Deliveryboy.
     *
     * @return void
     */
    public function sendEmail(): void
    {
        try {
            $deliveryboy = $this->deliveryboy->load($this->deliveryboyId);
            $deliveryboyName = $deliveryboy->getName();
            $templateVariables = [];
            $templateVariables["otp"] = $this->getOtp();
            $templateVariables["orderDate"] = $this->deliveryboyHelper->formatDateTimeCurrentLocale(
                $this->order->getCreatedAt()
            );
            $templateVariables["orderStatus"] = ($this->order->getStatus());
            $templateVariables["customerName"] = $this->order->getCustomerFirstname() .
                " " . $this->order->getCustomerLastname();
            $templateVariables["deliveryboyName"] = $deliveryboyName;
            $templateVariables["orderIncrementId"] = $this->incrementId;
            $templateVariables["deliveryboyContact"] = $deliveryboy->getMobileNumber();
            
            $senderInfo = [
                "name"  => ("Admin"),
                "email" => $this->deliveryboyHelper->getGeneralEmail()
            ];
            $receiverInfo = [
                "name"  => $templateVariables["customerName"],
                "email" => $this->order->getCustomerEmail()
            ];
            $template = "deliveryboy_email_otp";
            $this->inlineTranslation->suspend();
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
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
    }

    /**
     * Function is to send notification about new order assigned to deliveryboy
     *
     * @return void
     */
    public function sendAssignmentNotification()
    {
        $message = [
            "id" => $this->order->getId(),
            "body" => __("Your have received new order to deliver."),
            "title" => __("New Order Assigned."),
            "sound" => "default",
            "status" => $this->order->getStatus(),
            "message" => __("Your have received new order to deliver."),
            "incrementId" => $this->order->getIncrementId(),
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
        if ($authKey) {
            $tokenCollection = $this->getFreshTokenCollection();
            $tokenCollection
                ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
            $this->operationHelper->sendNotificationToSubscribedUsers(
                $authKey,
                $fields,
                $message,
                $tokenCollection
            );
        }
    }

    /**
     * Get Fresh TOken COllection.
     *
     * @return TokenCollection
     */
    public function getFreshTokenCollection()
    {
        $this->tokenResourceCollection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $tokenCollection = $this->tokenResourceCollection->getNewEmptyItem()->getCollection();
        return $tokenCollection;
    }

    /**
     * Send UnAssigned FPM Notification
     *
     * @return void
     */
    public function sendUnAssignmentNotification()
    {
        $message = [
            "id" => $this->order->getId(),
            "body" => __("One order is unassigned from you."),
            "title" => __("Order UnAssigned."),
            "sound" => "default",
            "status" => $this->order->getStatus(),
            "message" => __("One order is unassigned from you."),
            "incrementId" => $this->order->getIncrementId(),
            "notificationType" => "orderUnassigned"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        if ($authKey = $this->deliveryboyHelper->getFcmApiKey()) {
            $tokenCollection = $this->getFreshTokenCollection();
            $tokenCollection
                ->addFieldToFilter("deliveryboy_id", $this->alreadyAssignedTo);
            $this->operationHelper->sendNotificationToSubscribedUsers(
                $authKey,
                $fields,
                $message,
                $tokenCollection
            );
        }
    }
}
