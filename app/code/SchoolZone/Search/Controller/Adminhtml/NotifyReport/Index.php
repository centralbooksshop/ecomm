<?php

namespace SchoolZone\Search\Controller\Adminhtml\NotifyReport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('SchoolZone_Seach::NotifyReport');
        $resultPage->addBreadcrumb(__('SchoolZone'), __('SchoolZone'));
        $resultPage->addBreadcrumb(__('Manage Notify'), __('Manage Notify'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Notify'));

        return $resultPage;
    }
}
?>