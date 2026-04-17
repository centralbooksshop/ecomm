<?php
namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessDtdcOrder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Retailinsights_DtdcCustom::ProcessDtdcOrder';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Load the DTDC Order Processing page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Retailinsights_DtdcCustom::ProcessDtdcOrder');
        $resultPage->getConfig()->getTitle()->prepend(__('Process DTDC Orders'));
        return $resultPage;
    }
}
