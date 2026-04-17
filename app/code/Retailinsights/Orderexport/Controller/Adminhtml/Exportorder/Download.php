<?php
namespace Retailinsights\Orderexport\Controller\Adminhtml\Exportorder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Download
 */
class Download extends Action
{
    const MENU_ID = 'Retailinsights_Orderexport::export_exportorder';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Download constructor.
     *
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
     * Load the page defined in view/adminhtml/layout/orderexport_exportorder_index.xml
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Order Export'));

        return $resultPage;
    }
}
