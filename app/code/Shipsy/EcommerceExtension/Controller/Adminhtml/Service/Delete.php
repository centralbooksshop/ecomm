<?php
namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Service;

use Magento\Backend\App\Action;
use Shipsy\EcommerceExtension\Model\ServiceFactory;

class Delete extends Action
{
    protected $serviceFactory;

    public function __construct(
        Action\Context $context,
        ServiceFactory $serviceFactory
    ) {
        parent::__construct($context);
        $this->serviceFactory = $serviceFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $model = $this->serviceFactory->create()->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Service deleted successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
            }
        }
        return $this->_redirect('*/*/');
    }
}
