<?php

namespace Retailinsights\Cancellayer\Controller\Adminhtml\similarproductsattributes;

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
        $resultPage->setActiveMenu('Retailinsights_Cancellayer::similarproductsattributes');
        $resultPage->addBreadcrumb(__('Retailinsights'), __('Retailinsights'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Cancel and Refund'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cancel and Refund'));

        return $resultPage;
    }
}
?>