<?php

namespace Retailinsights\Replacelayer\Controller\Adminhtml\similarproductsattributes;

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
        $resultPage->setActiveMenu('Retailinsights_Replacelayer::similarproductsattributes');
        $resultPage->addBreadcrumb(__('Retailinsights'), __('Retailinsights'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Replacement Books/Items'));
        $resultPage->getConfig()->getTitle()->prepend(__('Replacement Books/Items'));

        return $resultPage;
    }
}
?>