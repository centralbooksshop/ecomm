<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Plumrocket\RMA\Controller\AbstractReturns;

class Create extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->getModel();
        if (! $model->hasOrderId()) {
            $orderId = $this->getRequest()->getParam('order_id');
            $model->setOrderId($orderId);
        }
        $this->registry->register('current_model', $model);

        // Load form data in local storage and clear form data from session.
        $this->dataHelper->getFormData();
        $this->dataHelper->setFormData(false);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $title = __('New Return');
        if ($order = $model->getOrder()) {
            $title = __('New Return for Order #%1', $order->getRealOrderId());
        }

        $this->preparePage($resultPage, [
            'title' => $title
        ]);

        return $resultPage;
    }

    /**
     * - need order id
     * - can create return for this order
     * - order belongs to customer
     * - order belongs to guest
     *
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        // Client cannot have return on this page
        return false;
    }
}
