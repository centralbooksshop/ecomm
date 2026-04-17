<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Address;

class Edit extends \Infomodus\Fedexlabel\Controller\Adminhtml\Address
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->addressFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('infomodus_fedexlabel/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_getSession()->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->coreRegistry->register('current_infomodus_fedexlabel_address', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('address_address_edit');
        $this->_view->renderLayout();
    }
}
