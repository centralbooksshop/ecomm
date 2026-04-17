<?php

namespace Retailinsights\SplitOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CopyQuoteItemToOrderItem implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        foreach ($quote->getAllItems() as $quoteItem) {

            $orderItem = $order->getItemByQuoteItemId($quoteItem->getId());
            if (!$orderItem) {
                continue;
            }

            // Copy custom fields
            $orderItem->setData('given_options', $quoteItem->getData('given_options'));
            $orderItem->setData('given_options_msg', $quoteItem->getData('given_options_msg'));
			$orderItem->setData('dispatch_status', 'not_confirmed');
			$orderItem->setData('delivery_status', 'not_confirmed');
        }
    }
}