<?php

namespace Centralbooks\OrderDashboards\Controller\Adminhtml\Reports;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Centralbooks_OrderDashboards::sync_cronreports';
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Centralbooks_OrderDashboards::sync_cronreports');
        $resultPage->getConfig()->getTitle()->prepend((__('Cron Reports')));
        return $resultPage;
    }
}
