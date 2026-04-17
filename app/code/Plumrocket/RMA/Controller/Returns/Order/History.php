<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns\Order;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\Page;
use Plumrocket\RMA\Controller\AbstractReturns;

class History extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (! $this->getGuestOrder()) {
            $request = $this->getRequest();
            $orderId = $request->getParam('order_id');
            $order = $this->orderFactory->create()->load($orderId);
            if ($order && $order->getId()) {
                $this->registry->register('current_order', $order);
            }
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->preparePage($resultPage, [
            'active' => 'sales/order/history'
        ]);

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareGuestPage(Page $resultPage, array $arguments = [])
    {
        // Add handle in guest mode for correct tab links
        $resultPage->addHandle('sales_order_guest_info_links');
        parent::prepareGuestPage($resultPage, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        // Client cannot have return id on this page
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // This page depends on order id but without checking if order can be returned
        $this->canReturnControl = false;
        return parent::canViewOrder();
    }
}
