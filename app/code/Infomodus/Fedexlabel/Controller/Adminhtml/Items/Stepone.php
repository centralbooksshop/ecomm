<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Stepone extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $order = $this->order->create()->loadByIncrementId($this->getRequest()->getParam('order_id'));
                $this->_redirect('infomodus_fedexlabel/*/edit', ['direction' => $this->getRequest()->getParam('direction'), 'order_id' => $order->getId()]);
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                    $this->_redirect('infomodus_fedexlabel/*/editone');
                return;
            }
        }
        $this->_redirect('infomodus_fedexlabel/*/');
    }
}
