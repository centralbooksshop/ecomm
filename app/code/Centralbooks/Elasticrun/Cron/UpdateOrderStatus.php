<?php
namespace Centralbooks\Elasticrun\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Centralbooks\Elasticrun\Helper\Data;
use Psr\Log\LoggerInterface;

class UpdateOrderStatus
{
    protected $orderCollectionFactory;
    protected $helper;
    protected $logger;
    protected $customLogger;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->helper = $helper;
        $this->logger = $logger;

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/elasticrun.log');
        $this->customLogger = new \Zend_Log();
        $this->customLogger->addWriter($writer);
    }

    public function execute()
    {
        $this->customLogger->info('ElasticRun Cron Started '.date("Y-m-d H:i:s"));
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;
        try {
            $orders = $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', ['in' => ['dispatched_to_courier']])
                ->addFieldToFilter('cbo_courier_name', 'Elasticrun')
                ->addFieldToFilter('cbo_reference_number', ['neq' => '']);
            $this->customLogger->info("ElasticRun no of orders processed is  ".count($orders));
            foreach ($orders as $order) {

                $consignment = trim($order->getData('cbo_reference_number'));
                if (!$consignment) {
                    continue;
                }

                $this->customLogger->info(
                    "Processing Order {$order->getIncrementId()} | Consignment: {$consignment}"
                );

                //$apiUrl = 'https://qc-libera.elasticrun.in/api/method/libera_integration.libera.api.consignment.get_order_detail_guest?consignment=' . $consignment;

                $response = $this->helper->executeCurl($consignment);
                $data = json_decode($response, true);

                $this->customLogger->info(
                    'API Response: ' . json_encode($data)
                );

                if (
                    isset($data['message']['docket_status'])
                ) {

					$docketStatus = strtolower(trim($data['message']['docket_status'] ?? ''));
					$docketStatus = str_replace(' ', '', $docketStatus);

					$docketStatusCode = strtolower(trim($data['message']['docket_status_code'] ?? ''));

					$this->customLogger->info(
						"Docket Status: {$docketStatus} | Code: {$docketStatusCode}"
					);

					if ($docketStatusCode === 'rto_ok' || $docketStatus === 'rtodelivered') {

						$order->setState(Order::STATE_COMPLETE)
							->setStatus('rto_returned')
							->addStatusHistoryComment(
								'ElasticRun RTO Delivered detected. Order marked as Not Delivered.'
							);

						$order->save();

						$this->customLogger->info(
							"Order {$order->getIncrementId()} marked NOT DELIVERED (RTO)"
						);

					} elseif ($docketStatus === 'delivered') {

						$order->setState(Order::STATE_COMPLETE)
							->setStatus('order_delivered')
							->addStatusHistoryComment(
								'Order marked as Delivered via ElasticRun Cron'
							);

						$order->save();

						$this->customLogger->info(
							"Order {$order->getIncrementId()} marked Delivered"
						);
					}
                }
                $processed++;
                sleep(1); // API safety
            }

        } catch (\Exception $e) {
           // $this->logger->error('ElasticRun Cron Error: ' . $e->getMessage());
            $this->customLogger->err($e->getMessage());
        }
        $endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->customLogger->info('ElasticRun Cron END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
            ' processed records '. $processed);
    }
}
