<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Index extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Infomodus_Fedexlabel::fedexlabel');
        $resultPage->getConfig()->getTitle()->prepend(__('FedEx labels'));
        $resultPage->addBreadcrumb(__('FedEx labels'), __('FedEx labels'));
        $resultPage->addBreadcrumb(__('Labels'), __('Labels'));
        return $resultPage;
    }
}
