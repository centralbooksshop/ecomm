<?php
namespace Magecomp\Cancelorder\Controller\Adminhtml\Cancelorder;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magecomp_Cancelorder::cancelorder');
        $resultPage->addBreadcrumb(__('Order Cancel'), __('Canceled Orders List'));
        $resultPage->addBreadcrumb(__('Order Cancel'), __('Canceled Orders List'));
        $resultPage->getConfig()->getTitle()->prepend(__('Canceled Orders List'));
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return true;
    }
}