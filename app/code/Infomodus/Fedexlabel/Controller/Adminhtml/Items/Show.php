<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Show extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        $label_ids = $this->getRequest()->getParam('label_ids', '');

        $order_id = $this->getRequest()->getParam('order_id', null);
        $shipment_id = $this->getRequest()->getParam('shipment_id', null);
        $type = $this->getRequest()->getParam('type', null);

        $redirect_path = $this->getRequest()->getParam('redirect_path', null);

        $labels = [];
        $model = [];
        if ($label_ids !== '') {
            $labels = $this->modelFactory->create()->getCollection()->addOrder('created_time', 'DESC')->addFieldToFilter('fedexlabel_id', ['in' => explode(',', $label_ids)]);
            $redirectLink = $this->getUrl('infomodus_fedexlabel/*/');
            $model['back_link'] = $redirectLink;
        } else if ($order_id !== null || $shipment_id !== null) {
            $labels = $this->modelFactory->create()->getCollection()->addOrder('created_time', 'DESC');
            if ($order_id !== null) {
                $labels->addFieldToFilter('order_id', $order_id);
            }
            if ($shipment_id !== null) {
                $labels->addFieldToFilter('shipment_id', $shipment_id);
            }
            if ($type !== null) {
                $labels->addFieldToFilter('type', $type);
            }
        }

        if ($redirect_path !== null) {
            if (count($labels) > 0) {
                $firstLabel = $labels->getFirstItem();
                $isParams = true;
                switch ($redirect_path) {
                    case 'order':
                        $varName = 'Order';
                        $redirect_path = 'order';
                        $redirect_path2 = 'order/view';
                        break;
                    case 'order_list':
                        $varName = 'Order';
                        $redirect_path = 'order';
                        $redirect_path2 = 'order/index';
                        $isParams = false;
                        break;
                    case 'shipment_list':
                        $varName = 'Shipment';
                        $redirect_path = 'shipment';
                        $redirect_path2 = 'shipment/index';
                        $isParams = false;
                        break;
                    case 'refund_list':
                        $varName = 'Creditmemo';
                        $redirect_path = 'creditmemo';
                        $redirect_path2 = 'creditmemo/index';
                        $isParams = false;
                        break;
                    case 'refund':
                        $varName = 'Creditmemo';
                        $redirect_path = 'creditmemo';
                        $redirect_path2 = 'creditmemo/view';
                        break;
                    default:
                        $varName = 'Shipment';
                        $redirect_path = 'shipment';
                        $redirect_path2 = 'shipment/view';
                        break;
                }
                $params = [];
                if($isParams){
                    $params = [$redirect_path . '_id' => $firstLabel->{'get' . $varName . 'Id'}()];
                }
                $backLink = $this->getUrl('sales/' . $redirect_path2, $params);
                $model['back_link'] = $backLink;
            }
        }

        $model['labels'] = $labels;
        $label_types = [];
        if (count($labels) > 0) {
            foreach ($labels as $label) {
                $label_types[] = $label->getType2();
            }
            if (count($label_types) > 0) {
                $label_types = array_unique($label_types);
            }
            $model['label_types'] = $label_types;

            $this->_coreRegistry->register('infomodus_fedexlabel_items_show', $model);
            $this->_initAction();
            $this->_view->getLayout()->getBlock('items_items_show');
            $this->_view->renderLayout();
        } else {
             $this->_redirect($this->getUrl('infomodus_fedexlabel/*'));
        }

    }
}
