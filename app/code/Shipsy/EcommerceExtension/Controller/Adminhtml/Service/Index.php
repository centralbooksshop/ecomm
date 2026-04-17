<?php
namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Service;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Shipsy_EcommerceExtension::menu';

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        // Set active menu and page title
        $resultPage->setActiveMenu('Shipsy_EcommerceExtension::menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Service Master'));

        return $resultPage;
    }
}
