<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\RMA\Controller\AbstractReturns;

class NewAction extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->messageManager->addSuccessMessage(
            __('To create a new return, please click on "return" link next to your order below')
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('sales/order/history');
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // This action works only for customers
        return (bool)$this->getCustomer();
    }
}
