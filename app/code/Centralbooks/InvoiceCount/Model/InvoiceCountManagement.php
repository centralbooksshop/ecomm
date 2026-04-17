<?php
namespace Centralbooks\InvoiceCount\Model;

use Centralbooks\InvoiceCount\Api\InvoiceCountManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class InvoiceCountManagement implements InvoiceCountManagementInterface
{
    protected $resource;
    protected $orderRepository;
    protected $invoiceRepository;

    public function __construct(
        ResourceConnection $resource,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->resource = $resource;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Update invoice count for multiple orders
     */
    public function updateInvoiceCount($data)
    {
        if (!is_array($data)) {
            throw new LocalizedException(__('Invalid request format — expected an array of records.'));
        }

        $connection = $this->resource->getConnection();
        $table = $connection->getTableName('invoice_download_count');
        $updated = [];

        foreach ($data as $record) {
            $orderId = trim((string)($record['order_id'] ?? ''));
            $invoiceId = trim((string)($record['invoice_id'] ?? ''));
            $count = (int)($record['invoice_count'] ?? 0);

            if ($orderId === '' || $invoiceId === '') {
                throw new LocalizedException(__('Order ID or Invoice ID missing.'));
            }

            // --- Validate order ---
            try {
                $this->orderRepository->get($orderId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                throw new LocalizedException(__('Order ID %1 not found.', $orderId));
            }

            // --- Validate invoice ---
            try {
                $this->invoiceRepository->get($invoiceId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                throw new LocalizedException(__('Invoice ID %1 not found for order %2.', $invoiceId, $orderId));
            }

            // --- Check existing record ---
            $select = $connection->select()
                ->from($table)
                ->where('order_id = ?', $orderId)
                ->where('invoice_id = ?', $invoiceId);

            $existing = $connection->fetchRow($select);

            if ($existing) {
                $newCount = (int)$existing['invoice_count'] + $count;
                $connection->update(
                    $table,
                    ['invoice_count' => $newCount],
                    ['id = ?' => $existing['id']]
                );
                $updated[] = [
                    'order_id' => $orderId,
                    'invoice_id' => $invoiceId,
                    'invoice_count' => $newCount
                ];
            } else {
                // Record not found insert new
                $connection->insert($table, [
                    'order_id' => $orderId,
                    'invoice_id' => $invoiceId,
                    'invoice_count' => $count
                ]);
                $updated[] = [
                    'order_id' => $orderId,
                    'invoice_id' => $invoiceId,
                    'invoice_count' => $count
                ];
            }
        }

        return [true, $updated];
    }
}
