<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Magento\Framework\App\RequestInterface;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class History extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->preparePage($resultPage, [
            'title' => __('My Returns')
        ]);

        return $resultPage;
    }

    /**
     * - order and return are missed
     * - only for customer
     *
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        // This page doesn't need return id and allowed only for customers
        return (bool)$this->getCustomer();
    }
}
