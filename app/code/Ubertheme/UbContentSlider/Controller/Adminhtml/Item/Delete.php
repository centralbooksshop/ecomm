<?php
/**
 *
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbContentSlider::item_delete';
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Delete item action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('item_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Ubertheme\UbContentSlider\Model\Item');
                $model->load($id);
                //delete item
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The slide item has been deleted.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['item_id' => $id]);
            }
        }

        // display error message
        $this->messageManager->addError(__('We can\'t find a slide item to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
