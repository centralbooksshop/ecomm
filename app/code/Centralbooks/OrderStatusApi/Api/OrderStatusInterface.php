<?php
namespace Centralbooks\OrderStatusApi\Api;

interface OrderStatusInterface
{
    /**
     * Check if order is processing, then update to assigned_to_picker
     *
     * @param int $orderId
     * @return string
     */
    public function checkAndUpdate($orderId);

	/**
     * Update order status to assigned_to_picker if currently in processing
     *
     * @param mixed $orderIds
     * @return array
     */
    public function updateOrderStatus($orderIds);
}
