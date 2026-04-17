<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class MassUploadForm extends Action
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
        $selected = $this->getRequest()->getParam('selected', []);
        if (is_string($selected)) {
            $selected = json_decode($selected, true);
        }

        // Store in registry or forward to block via layout
        $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->register('selected_order_ids', $selected);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Retailinsights_Orders::orders');
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Acknowledgement'));
        return $resultPage;
    }
}

