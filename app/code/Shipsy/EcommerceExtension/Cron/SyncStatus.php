<?php
namespace Shipsy\EcommerceExtension\Cron;

class SyncStatus {
    protected $orderRepository;
    protected $resourceConnection;
    protected $dataHelper;
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->resourceConnection = $resourceConnection;
        $this->dataHelper = $dataHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

	public function execute() {
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/dtdc_status.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('Sync Status Cron Started '.date("Y-m-d H:i:s"));

		// Allowed statuses from Shipsy
		$validStatuses = ['delivered'];

		$connection = $this->resourceConnection->getConnection();
		$table = $connection->getTableName('sales_order');
		$result = $connection->fetchAll("
			SELECT cbo_reference_number, increment_id, entity_id 
			FROM `{$table}` 
			WHERE status IN ('dispatched_to_courier') 
			AND cbo_reference_number IS NOT NULL
		");

		$customerReferenceNumberList = [];
		foreach ($result as $rowObj) {
			$customerReferenceNumberList[] = $rowObj['increment_id'];
		}

		if (count($customerReferenceNumberList)) {
			$logger->info('customerReferenceNumberList: ' . print_r($customerReferenceNumberList, true));

			$responsedata = $this->dataHelper->getConsignmentDetails($customerReferenceNumberList);
			$logger->info('responsedata: ' . json_encode($responsedata));

			if (isset($responsedata['data']) && is_array($responsedata['data'])) {
				foreach ($responsedata['data'] as $incrementId => $statusData) {
					$shipsyStatus = strtolower($statusData[0]['status']);

					if (in_array($shipsyStatus, $validStatuses, true)) {
						// Determine Magento status to set
						$magentoStatus = ($shipsyStatus === 'delivered') ? 'order_delivered' : $shipsyStatus;

						// Load Magento order by increment_id
						$searchCriteria = $this->searchCriteriaBuilder
							->addFilter('increment_id', $incrementId)
							->create();

						$orderList = $this->orderRepository->getList($searchCriteria)->getItems();
						foreach ($orderList as $order) {
							$orderId = $order->getId();
							$fetchedOrder = $this->orderRepository->get($orderId);

							// Update status
							$fetchedOrder->setStatus($magentoStatus);
							$this->orderRepository->save($fetchedOrder);

							// Log it
							$logger->info("Order {$incrementId} updated to status {$magentoStatus}");
						}
					}
					$processed++;
				}
			}
		}

		// $logger->info('Sync Status Cron Ended');
		$endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $logger->info('Shipsy Sync Status Cron END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
            ' processed records '. $processed);
	}

}
