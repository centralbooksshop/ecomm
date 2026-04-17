<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Boxes;

class Index extends \Infomodus\Fedexlabel\Controller\Adminhtml\Boxes
{
    /**
     * Account list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Infomodus_Fedexlabel::fedexlabel');
        $resultPage->getConfig()->getTitle()->prepend(__('FedEx: Boxes'));
        $resultPage->addBreadcrumb(__('Infomodus'), __('Infomodus'));
        $resultPage->addBreadcrumb(__('Boxes'), __('Boxes'));
        return $resultPage;
    }
}
