<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Controller\Adminhtml\Zones;

class Delete extends \Centralbooks\Zones\Controller\Adminhtml\Zones
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
                $model = $this->_objectManager->create(\Centralbooks\Zones\Model\Zones::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Zones.'));
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
        return $this->_authorization->isAllowed("Centralbooks_Zones::centralbooks_zones_zones");
    }
}

