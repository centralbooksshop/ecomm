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
namespace Webkul\DeliveryBoy\Cron;

class CheckOrder
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Token\CollectionFactory
     */
    private $deliveryboyTokenResourceCollectionFactory;
    
    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    private $deliveryboyOrderResourceCollectionFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Operation
     */
    private $operationHelper;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    private $deliveryboyDataHelper;
    
    /**
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyDataHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Token\CollectionFactory $deliveryboyTokenResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyDataHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\ResourceConnection $connection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Token\CollectionFactory $deliveryboyTokenResourceCollectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
    ) {
        $this->operationHelper = $operationHelper;
        $this->deliveryboyDataHelper = $deliveryboyDataHelper;
        $this->jsonHelper = $jsonHelper;
        $this->connection = $connection;
        $this->deliveryboyTokenResourceCollectionFactory = $deliveryboyTokenResourceCollectionFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
    }

    /**
     * Send FCM notification
     *
     * @return void
     */
    public function execute(): void
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/webkuldeliveryboycrontracking.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Wekul delivery boy cron is started ".date("Y-m-d H:i:s"));
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;
        $orderCollection = $this->deliveryboyOrderResourceCollectionFactory
            ->create()
            ->addFieldToFilter("assign_status", ["nin" => ["0", "1"]])
            ->addFieldToFilter("deliveryboy_id", ["gt" => 0]);
		$orderCollection->addFieldToFilter('order_status', array('nin' => array('new')));
		$orderCollection->addFieldToFilter('order_status', array('nin' => array('canceled')));
		$orderCollection->addFieldToFilter('order_status', array('nin' => array('complete')));
		$orderCollection->addFieldToFilter('order_status', array('nin' => array('processing')));
        
        $salesTable = $this->connection->getTableName("sales_order");
        $orderCollection->getSelect()
            ->join(
                [
                    "salesOrder" => $salesTable
                ],
                "main_table.order_id=salesOrder.entity_id",
                [
                    "shipping_method" => "shipping_method"
                ]
            );
        $allowedShipping = explode(
            ",",
            $this->deliveryboyDataHelper->getAllowedShipping()
        );
        $orderCollection->addFieldToFilter(
            "shipping_method",
            [
                "in" => $allowedShipping
            ]
        );
        $authKey = $this->deliveryboyDataHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $tokenCollection = $this->deliveryboyTokenResourceCollectionFactory
            ->create()
            ->addFieldToFilter("is_admin", 1);
        $message = [
            "title" => __("Please reassign this Unclaimed Order"),
            "sound" => "default",
            "message" => __("Please reassign this Unclaimed Order"),
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "to" => '',
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
          $logger->info("Wekul delivery boy no of orders processed is  ".count($orderCollection));
        if ($authKey != "") {
            foreach ($orderCollection as $order) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info('msg to print');
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info(json_encode($order->getOrderStatus()));
                $orderIncrementId = $order->getIncrementId();
                $message['id'] = $orderIncrementId;
                $message["body"] = __("Unclaimed Order") . " #" . $orderIncrementId;
                $message['status'] = $order->getOrderStatus();
                $message['incrementId'] = $orderIncrementId;
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info(json_encode($message));
                foreach ($tokenCollection as $eachToken) {
                    $fields['to'] = $eachToken->getToken();
                    $fields["data"] = $message;
                    if ($eachToken->getOs() == "ios") {
                        $fields["notification"] = $message;
                    }
                    $result = $this->operationHelper->send($headers, $fields);
                    \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info(json_encode($result));
                    if (count($result) !== 0) {
                        if ($result["success"] == 0 && $result["failure"] == 1) {
                            $eachToken->delete();
                        }
                    }
                }
                $processed++;
            }
        }
        $endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $logger->info('Wekul delivery boy END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
            ' processed records '. $processed);
    }

    /**
     * Is valid Json.
     *
     * @param string $string is any string to test
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}
