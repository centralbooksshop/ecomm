<?php
namespace Centralbooks\OrderStatusApi\Model;

use Centralbooks\OrderStatusApi\Api\OrderStatusInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class OrderStatus implements OrderStatusInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAndUpdate($orderId)
    {
        try {
            // orderId could be numeric id or increment id depending on usage.
            // Here we assume numeric entity id. If you want to support increment_id, adjust accordingly.
            $order = $this->orderRepository->get($orderId);

            if ($order->getStatus() === 'processing') {
                $order->setStatus('assigned_to_picker');
                $order->setState('processing'); // keep same state
                $this->orderRepository->save($order);

                return "Order ID {$orderId} updated to assigned_to_picker.";
            } else {
                return "Order ID {$orderId} is not in processing status (current: {$order->getStatus()}).";
            }
        } catch (\Exception $e) {
            $this->logger->error('OrderStatusApi error: ' . $e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        }
    }

	public function updateOrderStatus($orderIds)
	{
		$updated = [];
		$skipped = [];

		if (!is_array($orderIds)) {
			$orderIds = [$orderIds];
		}

		foreach ($orderIds as $orderId) {
			try {
				$order = $this->orderRepository->get($orderId);

				if ($order->getStatus() === 'processing') {
					$order->setStatus('assigned_to_picker');
					$order->addCommentToStatusHistory('Order auto-assigned to picker via API.');
					$this->orderRepository->save($order);
					$updated[] = $orderId;
				} else {
					$skipped[] = $orderId;
				}

			} catch (\Exception $e) {
				$this->logger->error('OrderStatusApi error for order ' . $orderId . ': ' . $e->getMessage());
				$skipped[] = $orderId;
			}
		}

		// If no orders were updated, return an error (HTTP 400)
		if (count($updated) === 0) {
			throw new \Magento\Framework\Webapi\Exception(
				new \Magento\Framework\Phrase('No orders were updated. All skipped or invalid.'),
				0,
				\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
			);
		}

		// Otherwise return success (HTTP 200)
		return [
			'success' => true,
			'updatedOrders' => $updated,
			'skippedOrders' => $skipped,
			'message' => sprintf(
				'%d order(s) updated to assigned to picker. %d order(s) skipped because their status is not processing.',
				count($updated),
				count($skipped)
			)
		];
	}
}
