<?php
namespace Delhivery\Lastmile\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Delhivery\Lastmile\Helper\Data;
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

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/delhivery.log');
        $this->customLogger = new \Zend_Log();
        $this->customLogger->addWriter($writer);
    }

    public function execute()
    {
        $this->customLogger->info("Delhivery cron is started ".date("Y-m-d H:i:s"));
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;
        try {
            $orders = $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', ['in' => ['dispatched_to_courier']])
                ->addFieldToFilter('cbo_courier_name', 'Delhivery')
                ->addFieldToFilter('cbo_reference_number', ['neq' => '']);
            $this->customLogger->info("Delhivery no of orders processed is  ".count($orders));
            foreach ($orders as $order) {

                $awb = trim($order->getData('cbo_reference_number'));
                if (!$awb) {
                    continue;
                }

                $this->customLogger->info(
                    "Processing Order {$order->getIncrementId()} | AWB: {$awb}"
                );

                $apiUrl = $this->helper->getApiUrl('syncAWB');
                $token  = trim(
                    $this->helper->getScopeConfig('delhivery_lastmile/general/license_key')
                );

                if (!$apiUrl || !$token) {
                    $this->customLogger->err('Delhivery API URL or Token missing');
                    continue;
                }

                // SAME API FORMAT AS ADMIN CONTROLLER
                $path = $apiUrl . 'json/?verbose=0&token=' . $token . '&waybill=' . $awb;

                $response = $this->helper->Executecurl($path, '', '');
                $data = json_decode($response);

                $this->customLogger->info(
                    'API Response: ' . json_encode($data)
                );

                if (
                    isset($data->ShipmentData[0]->Shipment->Status->Status)
                ) {
                    $status = strtolower(
                        preg_replace(
                            '/\s+/',
                            '',
                            $data->ShipmentData[0]->Shipment->Status->Status
                        )
                    );

                    if ($status === 'delivered') {

                        $order->setState(Order::STATE_COMPLETE)
                            ->setStatus('order_delivered')
                            ->addStatusHistoryComment(
                                'Order marked as Delivered via Delhivery Cron'
                            );
                        $order->save();

                        $this->logger->info(
                            "Order {$order->getIncrementId()} marked Delivered"
                        );

                        $this->customLogger->info(
                            "Order {$order->getIncrementId()} marked Delivered"
                        );
                    }  elseif (strtoupper($status) === 'RTO') {

							$order->setState(Order::STATE_COMPLETE)
								->setStatus('rto_returned')
								->addStatusHistoryComment(
									'Order marked as RTO via Delhivery Cron'
								);
							$order->save();

							$this->logger->info(
								"Order {$order->getIncrementId()} marked as RTO"
							);

							$this->customLogger->info(
								"Order {$order->getIncrementId()} marked as RTO"
							);
						}
                }
                $processed++;
                sleep(1); // API safety
            }

        } catch (\Exception $e) {
          //  $this->logger->error('Delhivery Cron Error: ' . $e->getMessage());
            $this->customLogger->err($e->getMessage());
        }
        $endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->customLogger->info('Delhivery CRON END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
            ' processed records '. $processed);
    }
}
