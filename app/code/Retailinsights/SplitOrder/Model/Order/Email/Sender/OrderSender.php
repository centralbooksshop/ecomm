<?php

namespace Retailinsights\SplitOrder\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender {

    public function send(Order $order, $forceSyncMode = false)
    {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         $splitOrderHelper = $objectManager->get('Retailinsights\SplitOrder\Helper\Data');
         $order_id = $order->getEntityId();
         $increament_id = $order->getIncrementId();

         if($order->getIsBackeorderedItems() == 'Yes' && (strpos(strval($increament_id), '-') !== false)) {
             return false;
         }

        if($splitOrderHelper->IsSplitOrder($order_id)){
            return false;
        }
        $order->setSendEmail(true);

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            if ($this->checkAndSend($order)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $order->setEmailSent(null);
            $this->orderResource->saveAttribute($order, 'email_sent');
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }
}