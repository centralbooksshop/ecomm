<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Controller\Adminhtml\Locationcode;

class Delete extends \Centralbooks\LocationCode\Controller\Adminhtml\Locationcode
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('locationcode_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Centralbooks\LocationCode\Model\Locationcode::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Locationcode.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['locationcode_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Locationcode to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

