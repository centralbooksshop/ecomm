<?php
namespace Centralbooks\Amazon\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Centralbooks\Amazon\Helper\Auth;
use Psr\Log\LoggerInterface;

class UpdateOrderStatus
{
    protected $orderCollectionFactory;
    protected $authHelper;
    protected $logger;
    protected $customLogger;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        Auth $authHelper,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->authHelper = $authHelper;
        $this->logger = $logger;

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/amazon_tracking.log');
        $this->customLogger = new \Zend_Log();
        $this->customLogger->addWriter($writer);
    }

    public function execute()
    {
        $this->customLogger->info("Amazon Tracking cron is started ".date("Y-m-d H:i:s"));
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;
        try {
            $accessToken = $this->authHelper->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('Amazon access token not available');
            }

            $orders = $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', ['in' => ['dispatched_to_courier']])
                ->addFieldToFilter('cbo_courier_name', 'Amazon')
                ->addFieldToFilter('cbo_reference_number', ['neq' => '']);
            $this->customLogger->info("Amazon Tracking no of orders processed is  ".count($orders));
            foreach ($orders as $order) {
                $trackingId = trim($order->getData('cbo_reference_number'));
                if (!$trackingId) {
                    continue;
                }

                $this->customLogger->info(
                    "Order {$order->getIncrementId()} | Tracking ID: {$trackingId}"
                );

                $response = $this->callAmazonTrackingApi($trackingId, $accessToken);
                if (!$response) {
                    continue;
                }

                $this->customLogger->info('API Response: ' . json_encode($response));

                $status = $response['payload']['summary']['status'] ?? '';

                if (strtolower($status) === 'delivered') {

                    $order->setState(Order::STATE_COMPLETE)
                        ->setStatus('order_delivered')
                        ->addStatusHistoryComment(
                            'Order marked Delivered via Amazon Tracking Cron'
                        );
                    $order->save();

                    $this->logger->info(
                        "Order {$order->getIncrementId()} marked Delivered"
                    );
                    $this->customLogger->info(
                        "Order {$order->getIncrementId()} marked Delivered"
                    );
                }
                $processed++;
                sleep(1); // API rate safety
            }

        } catch (\Exception $e) {
          //  $this->logger->error('Amazon Tracking Cron Error: ' . $e->getMessage());
            $this->customLogger->err($e->getMessage());
        }
        $endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->customLogger->info('Amazon Tracking Cron END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
                ' processed records '. $processed);
    }

    protected function callAmazonTrackingApi($trackingId, $accessToken)
    {
        $url = 'https://sellingpartnerapi-eu.amazon.com/shipping/v2/tracking'
             . '?carrierId=ATS'
             . '&trackingId=' . urlencode($trackingId);

        $headers = [
            "Content-Type: application/json",
            "x-amz-access-token: {$accessToken}",
            "x-amzn-shipping-business-id: AmazonShipping_IN"
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error || $code !== 200) {
            $this->customLogger->err(
                "Tracking API Error | HTTP {$code} | {$response}"
            );
            return null;
        }

        return json_decode($response, true);
    }
}
