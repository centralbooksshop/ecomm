<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;

class PrintAction extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->getModel();
        $this->registry->register('current_model', $model);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $title = __('Return #%1', $model->getIncrementId());
        $resultPage->addHandle('print');
        $this->preparePage($resultPage, [
            'title' => $title
        ]);

        return $resultPage;
    }

    /**
     * - need return id
     * - return belongs to customer
     * - return belongs to guest
     * - admin code exists
     *
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        if ($this->specialAccess()) {
            return true;
        }

        return parent::canViewReturn();
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client cannot have separate order on this page
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function specialAccess()
    {
        // Access by code for admin.
        $model = $this->getModel();
        $request = $this->getRequest();
        $code = $this->returnsHelper->getCode($model, ReturnsHelper::CODE_SALT_PRINT);
        if ($request->getParam('code')
            && $request->getParam('code') === $code
        ) {
            return true;
        }

        return false;
    }
}
