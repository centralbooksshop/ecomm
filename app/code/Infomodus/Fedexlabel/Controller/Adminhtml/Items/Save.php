<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Save extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $order_id = $this->getRequest()->getParam('order_id');
            $type = $this->getRequest()->getParam('type');
            $shipment_id = $this->getRequest()->getParam('shipment_id', null);
            $redirectUrl = $this->getRequest()->getParam('redirect_path', null);
            $params = $this->getRequest()->getParams();
            $order = $this->orderRepository->get($order_id);

            if ($params['package']) {
                $arrPackagesOld = $params['package'];
                if (count($arrPackagesOld) > 0) {
                    foreach ($arrPackagesOld as $k => $v) {
                        $i = 0;
                        foreach ($v as $d => $f) {
                            $arrPackages[$i][$k] = $f;
                            $i += 1;
                        }
                    }

                    unset($v, $k, $i, $d, $f);
                    $params['package'] = $arrPackages;
                }

                $label = $this->_handy->getLabel($order, $type, $shipment_id, $params);
                if (true === $label) {
                    $label_ids = [];
                    if (count($this->_handy->label) > 0) {
                        foreach ($this->_handy->label as $label) {
                            $label_ids[] = $label->getId();
                        }
                    }

                    if (count($this->_handy->label2) > 0) {
                        foreach ($this->_handy->label2 as $label2) {
                            $label_ids[] = $label2->getId();
                        }
                    }

                    $this->_redirect(
                        'infomodus_fedexlabel/items/show',
                        [
                            'label_ids' => implode(',', $label_ids),
                            'type' => $type,
                            'redirect_path' => $redirectUrl,
                        ]
                    );

                    return;
                } else {
                    $this->messageManager->addErrorMessage(__('Error 10002.'));
                    $this->_redirect('infomodus_fedexlabel/*');
                    return;
                }
            } else {
                $this->messageManager->addErrorMessage(__('There must be at least one package. Add the package please'));
                $this->_redirect('infomodus_fedexlabel/*');
                return;
            }
        }

        $this->_redirect('infomodus_fedexlabel/*/');
    }
}
