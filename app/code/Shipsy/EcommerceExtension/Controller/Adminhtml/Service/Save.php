<?php
namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Service;

use Magento\Backend\App\Action;
use Shipsy\EcommerceExtension\Model\ServiceFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Shipsy_EcommerceExtension::menu';

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
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->messageManager->addErrorMessage(__('No data to save.'));
            return $this->_redirect('*/*/');
        }

        try {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->serviceFactory->create();

            if ($id) {
                $model->load($id);
            }

            $model->addData($data);
            $model->save();

            $this->messageManager->addSuccessMessage(__('Service saved successfully.'));

            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('*/*/edit', ['entity_id' => $model->getId()]);
            }

            return $this->_redirect('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
            return $this->_redirect('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
    }
}
