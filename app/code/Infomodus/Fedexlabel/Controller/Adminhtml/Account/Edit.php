<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Account;

class Edit extends \Infomodus\Fedexlabel\Controller\Adminhtml\Account
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->account;

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('infomodus_fedexlabel/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->context->getSession()->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_infomodus_fedexlabel_account', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('account_account_edit');
        $this->_view->renderLayout();
    }
}
