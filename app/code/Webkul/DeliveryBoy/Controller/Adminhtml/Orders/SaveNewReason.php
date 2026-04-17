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
use Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory as DeliveryboyResourceCollectionFactory;

class SaveNewReason extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\DeliveryBoy\Model\ReasonFactory $reasonF
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
	 protected $deliveryBoy;
	 protected $collectionFactory;
	 protected $deliveryboyOrderFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\DeliveryBoy\Model\ReasonFactory $reasonF,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		DeliveryboyResourceCollectionFactory $deliveryBoy,
		\Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
		\Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->reasonF = $reasonF;
        $this->datetime = $datetime;
		$this->deliveryBoy = $deliveryBoy;
		$this->collectionFactory = $collectionFactory;
		$this->deliveryboyOrderFactory = $deliveryboyOrderFactory;
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
                ->addFieldToSelect("name")
                ->addFieldToSelect("id")
                ->addFieldToSelect("mobile_number")
                ->addFieldToSelect("status")
                ->addFieldToSelect("availability_status")
                ->addFieldToFilter("id", $senderId)
                ->getFirstItem();
			 $name = $drivercollection->getName();
			} else {
              $name = "Admin";
			}
             //echo '<pre>';
			 //print_r($wholeData);
			 //print_r($order->debug()); // Print Order Object
		    //die;
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
			
			$deliveryboyOrder = $this->collectionFactory->create();
			$deliveryboyOrder->addFieldToFilter("increment_id", $incrementId);
			$deliveryboyOrdercoll = $deliveryboyOrder->getFirstItem();
            $deliveryboy_id = $deliveryboyOrdercoll->getId();

			$deliveryboyOrdermain = $objectManager->create('\Webkul\DeliveryBoy\Model\Order')->load($deliveryboy_id);
			$deliveryboyOrdermain->setOrderStatus('order_not_delivered');
			//$deliveryboyOrdermain->setStatus('order_not_delivered');
            $deliveryboyOrdermain->save();
			//$deliveryboyOrdermain = $this->deliveryboyOrderFactory->create();
			 //echo '<pre>';
			 //print_r($deliveryboyOrdermain->getData());
			 //die;
            $this->reasonF->create()->setReason($reason)
                ->setSenderId($senderId)
                ->setIsDeliveryboy($isDeliveryboy)
                ->setOrderIncrementId($incrementId)
                ->setDeliveryboyOrderId($deliveryboyOrderId)
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
