<?php
namespace Retailinsights\Orders\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

class OrderDeliveredEmail implements ObserverInterface
{
    protected $transportBuilder;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        // Only trigger if status = order_delivered
        if ($order->getStatus() !== 'order_delivered') {
            return;
        }

        $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();

        // Separate pending items
        $willBeGivenItems = "<ul>";
        $schoolIssuedItems = "<ul>";

        foreach ($order->getAllItems() as $item) {
            if ((int)$item->getOrderId() === (int)$order->getId() && $item->getGivenOptions()) {
                if ($item->getGivenOptions() == 1) {
                    $willBeGivenItems .= "<li>" . $item->getName() . "</li>";
                } elseif ($item->getGivenOptions() == 2) {
                    $schoolIssuedItems .= "<li>" . $item->getName() .  "</li>";
                }
            }
        }

        $willBeGivenItems .= "</ul>";
        $schoolIssuedItems .= "</ul>";

        // If no pending items, don’t send email
        if ($willBeGivenItems === "<ul></ul>" && $schoolIssuedItems === "<ul></ul>") {
            return;
        }

        // Example dynamic last date (7 days from now)
        $lastDate = date('d-m-Y', strtotime('+7 days'));

        $link = 'https://centralbooksonline.com/schools/register-school'; // Change as needed

        // Combine both sections for email
        $pendingItems = '';
        if ($willBeGivenItems !== "<ul></ul>") {
            $pendingItems .= "<p><strong>Will be given items:</strong></p>" . $willBeGivenItems;
        }
        if ($schoolIssuedItems !== "<ul></ul>") {
            $pendingItems .= "<p><strong>School-issued items:</strong></p>" . $schoolIssuedItems;
        }

        // Prepare email
        $transport = $this->transportBuilder
            ->setTemplateIdentifier('order_delivered_email_template') // from email_templates.xml
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $order->getStoreId()
            ])
            ->setTemplateVars([
                'customer_name' => $customerName,
                'order' => $order,
                'pending_items' => $pendingItems,
                'last_date' => $lastDate,
                'link' => $link
            ])
            ->setFrom('general') // set sender from store config
            ->addTo($order->getCustomerEmail(), $customerName)
            ->getTransport();

        $transport->sendMessage();
    }
}
