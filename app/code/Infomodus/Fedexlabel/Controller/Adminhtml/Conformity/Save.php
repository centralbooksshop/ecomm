<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Conformity;

class Save extends \Infomodus\Fedexlabel\Controller\Adminhtml\Conformity
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data =[];
            try {
                $model = $this->modelFactory->create();
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                $data['country_ids'] = implode(',', $data['country_ids']);
                $model->setData($data);
                $this->_getSession()->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $this->_getSession()->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('infomodus_fedexlabel/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('infomodus_fedexlabel/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('infomodus_fedexlabel/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('infomodus_fedexlabel/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_getSession()->setPageData($data);
                $this->_redirect('infomodus_fedexlabel/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('infomodus_fedexlabel/*/');
    }
}
