<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Autoprint extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $shipment_id = $this->getRequest()->getParam('shipment_id', null);
        $labelId = $this->getRequest()->getParam('label_id', null);
        $type = $this->getRequest()->getParam('type');
        $type_print = $this->getRequest()->getParam('type_print', 'auto');
        $path = $this->_handy->_conf->getBaseDir('media') . '/fedexlabel/label/';

        $order = $this->orderRepository->get($order_id);
        $storeId = $order->getStoreId();

        $fedexlabel = $this->modelFactory->create();
        if($type_print === 'auto') {
            if ($labelId === null) {
                $colls2 = $fedexlabel->getCollection()->addFieldToFilter('order_id', $order_id);
                if ($shipment_id !== null) {
                    $colls2->addFieldToFilter('shipment_id', $shipment_id);
                }
                $colls2->addFieldToFilter('type', $type)->addFieldToFilter('lstatus', 0);
                foreach ($colls2 as $coll) {
                    if (file_exists($path . ($coll->getLabelname()))) {
                        if ($data = file_get_contents($path . ($coll->getLabelname()))) {
                            $this->_handy->_conf->sendPrint($data, $storeId);
                        }
                    }
                }
                $this->getResponse()
                    ->setContent(__('Label was sent to print'));
            } elseif ($labelId !== null) {
                $label = $fedexlabel->load($labelId);
                if ($label && file_exists($path . $label->getLabelname())) {
                    if ($data = file_get_contents($path . ($label->getLabelname()))) {
                        $this->_handy->_conf->sendPrint($data, $storeId);
                    }
                }
                $this->getResponse()
                    ->setContent(__('Label was sent to print'));
            }
        } else {
            $label = $fedexlabel->load($labelId);
            $this->getResponse()
                ->setContent(file_get_contents($path.$label->getLabelname()));
        }
        return;
    }
}
