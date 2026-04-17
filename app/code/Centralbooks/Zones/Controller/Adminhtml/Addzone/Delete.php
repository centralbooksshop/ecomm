<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Controller\Adminhtml\Addzone;

class Delete extends \Centralbooks\Zones\Controller\Adminhtml\Addzone
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
        $id = $this->getRequest()->getParam('zones_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Centralbooks\Zones\Model\Addzone::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Zone.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['zones_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Zones to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

	 /**
     * Authorize current admin user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Centralbooks_Zones::addzone_index");
    }
}

