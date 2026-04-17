<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class UploadForm extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ){
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $itemId = $this->getRequest()->getParam('item_id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Retailinsights_Orders::orders');
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Acknowledgement'));
        return $resultPage;
    }
}
