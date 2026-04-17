<?php
namespace Retailinsights\Orders\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Psr\Log\LoggerInterface;

class LogOrderStatusChange implements ObserverInterface
{
    private $timezone;
    private $adminSession;
    private $logger;

    public function __construct(
        TimezoneInterface $timezone,
        AdminSession $adminSession,
        LoggerInterface $logger
    ) {
        $this->timezone = $timezone;
        $this->adminSession = $adminSession;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        // Get old and new status
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();

        // No change — skip
        if ($oldStatus === $newStatus) {
            return;
        }

        // Get admin username
        $username = 'System';
        try {
            $adminUser = $this->adminSession->getUser();
            if ($adminUser && $adminUser->getUserName()) {
                $username = $adminUser->getUserName();
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                'OrderStatusLogger: admin session not available: ' . $e->getMessage()
            );
        }

        $timestamp = $this->timezone->date()->format('Y-m-d H:i:s');

        // Corrected log message format
        $message = sprintf(
            '[%s] Status changed from %s to %s by %s',
            $timestamp,
            $oldStatus ?: 'N/A',
            $newStatus ?: 'N/A',
            $username
        );

        // Add comment
        try {
            $order->addStatusHistoryComment($message)
                ->setIsCustomerNotified(false);
        } catch (\Exception $e) {
            $this->logger->error(
                'OrderStatusLogger: failed to add history comment: ' . $e->getMessage()
            );
        }
    }
}
