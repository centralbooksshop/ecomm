<?php
namespace Retailinsights\Orders\Cron;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class UpdateCourierAvailability
{
    protected $resource;
    protected $logger;

    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->logger   = $logger;
    }

    /**
     * Cron job to update courier_available field in sales_order
     * by matching pincodes with available couriers.
     */
    public function execute()
    {
        $updatedCount = 0; //  define before use
	    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/retailordercrontracking.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Retailinsights Update Courier Availability cron is started ".date("Y-m-d H:i:s"));
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;
        try {
            $connection       = $this->resource->getConnection();
            $salesOrderTable  = $connection->getTableName('sales_order');
            $addressTable     = $connection->getTableName('sales_order_address');
            $courierTable     = $connection->getTableName('retailinsights_courieravailability_courier');

            //  Run for today's date or a custom date
            $targetDate  = date('Y-m-d'); // Example: 2025-11-17
            $startOfDay  = $targetDate . ' 00:00:00';
            $endOfDay    = $targetDate . ' 23:59:59';

            $this->logger->info(" Cron Started: Updating courier availability for {$targetDate}");

            //  Single optimized SQL query (no loop)
            $sql = "
                UPDATE {$salesOrderTable} AS so
                INNER JOIN {$addressTable} AS sa ON so.entity_id = sa.parent_id
                INNER JOIN (
                    SELECT pincode,
                           GROUP_CONCAT(DISTINCT courier_name ORDER BY courier_name SEPARATOR ', ') AS couriers
                    FROM {$courierTable}
                    WHERE is_available = 1
                    GROUP BY pincode
                ) AS c ON sa.postcode = c.pincode
                SET so.courier_available = c.couriers
                WHERE sa.address_type = 'shipping'
                  AND so.created_at >= :dateStart
                  AND so.created_at < :dateEnd
            ";
			$this->logger->info(" Query {$sql}");

            $statement = $connection->query($sql, [
                'dateStart' => $startOfDay,
                'dateEnd'   => $endOfDay
            ]);

            //  count affected rows
            $updatedCount = $statement->rowCount();

            $this->logger->info(" Cron Success: Courier availability updated for {$updatedCount} orders on {$targetDate}.");
	    $endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $logger->info('Retailinsights Update Courier Availability END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
            ' updated records '. $updatedCount);
        } catch (\Exception $e) {
            $this->logger->error(' Cron Error (Courier Availability): ' . $e->getMessage());
        }
    }
}

