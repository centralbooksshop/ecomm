<?php
namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessedDtdcOrdersList;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Retailinsights_DtdcCustom::ProcessedDtdcOrdersList');
        $resultPage->getConfig()->getTitle()->prepend(__('Processed Orders List'));
        return $resultPage;
    }
}
