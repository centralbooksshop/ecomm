<?php
namespace Retailinsights\ProcessCBOOrders\Controller\Adminhtml\Orders;

use Magento\Framework\Exception\LocalizedException;

class SaveNewReason extends \Magento\Backend\App\Action
{
     
	 /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Retailinsights\ProcessCBOOrders\Model\ReasonFactory $reasonF
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
	 
	 protected $deliveryBoy;
	 protected $collectionFactory;
	 protected $reasonF;
     public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Retailinsights\ProcessCBOOrders\Model\ReasonFactory $reasonF,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		\Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers\CollectionFactory $deliveryBoy,
		\Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\CollectionFactory $collectionFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->reasonF = $reasonF;
        $this->datetime = $datetime;
		$this->deliveryBoy = $deliveryBoy;
		$this->collectionFactory = $collectionFactory;
		$this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Save new Deliveryboy reason.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $wholeData = $this->getRequest()->getParams();
            try {
            $reason = $wholeData["reason"] ?? "";
            $senderId = $wholeData["senderId"] ?? 0;
            $incrementId = $wholeData["incrementId"] ?? "";
            $isDeliveryboy = $wholeData["isDeliveryboy"] ?? false;
            $deliveryboyOrderId = $wholeData["deliveryboyOrderId"] ?? 0;

            if ($reason == "") {
                throw new LocalizedException(__("Reason field is required."));
            }
            if (str_word_count($reason < 5)) {
                throw new LocalizedException(__("Reason should be atleast 5 words."));
            }

            if ($senderId) {
             $drivercollection = $this->deliveryBoy->create()
                ->addFieldToSelect("driver_name")
                ->addFieldToSelect("id")
                ->addFieldToSelect("driver_mobile")
                ->addFieldToFilter("id", $senderId)
                ->getFirstItem();
			   $name = $drivercollection->getDriverName();
			} else {
              $name = "Admin";
			}
             
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		    $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            $orderId = $orderInfo->getId();
			$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $state = $order->getState();
            $status = 'order_not_delivered';
            $comment = 'order status is order_not_delivered';
            $isNotified = false;
            $order->setState('complete');
            $order->setStatus($status);
            $order->addStatusToHistory($order->getStatus(), $comment);
            $order->save();
			
			  
			   $this->reasonF->create()
				->setReason($reason)
                ->setDriverId($senderId)
                ->setOrderIncrementId($incrementId)
                ->setCommentedBy($name)
                ->setCreatedAt($this->datetime->gmtDate())
                ->save();

            $result = $this->resultJsonFactory->create();
            return $result->setData(1);
       } catch (\Throwable $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(0);
			//return $e->getMessage();
        }
    }
}
