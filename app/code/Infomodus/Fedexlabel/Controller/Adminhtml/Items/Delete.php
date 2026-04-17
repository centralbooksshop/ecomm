<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Delete extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {   

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('core_config_data');

        $sql = $connection->select()->from($tableName)->where('path = ?', 'admin/url/custom');
        $result = $connection->fetchAll($sql);  
        $url1 = $result[0]['value'];


        // $resultRedirect = $this->resultRedirectFactory->create();
        // $resultRedirect->setPath($return_url);

        $redirect = $this->getRequest()->getParam('redirect', 1);
        try {
            $shipment_id = null;
            $order_id = null;
            $type = 'label_ids';
            $shipidnumber = $this->getRequest()->getParam('label_ids', null);
            if ($shipidnumber !== null && !is_array($shipidnumber)) {
                $shipidnumber[0] = $shipidnumber;
                $this->messageManager->addSuccess(__('You deleted the item(s).'));
            } elseif (is_array($shipidnumber)) {
                $shipidnumber = array_unique($shipidnumber);
            } else {
                $shipidnumber = $this->getRequest()->getParam('shipidnumber', null);
                if ($shipidnumber !== null){
                    $shipidnumber[0] = $shipidnumber;
                    $type = "shipidnumber";
                }
            }

            if ($this->_handy->deleteLabel($shipidnumber, $type)) {
                if ($shipment_id === null) {
                    $shipment_id = $this->_handy->shipment_id;
                }
                if ($order_id === null) {
                    $order_id = $this->_handy->order->getId();
                }

                if ($redirect == 1) {
                    $this->_redirect('infomodus_fedexlabel/items/index');
                } else {
                    $redirect_path = $this->getRequest()->getParam('redirect_path');
                    if ($redirect_path == 'order' || $redirect_path == 'order_list') {
                        $this->_redirect('sales/order/view', ['order_id' => $order_id]);
                    } elseif ($redirect_path == 'shipment' || $redirect_path == 'shipment_list') {
                        $this->_redirect('sales/shipment/view', ['shipment_id' => $shipment_id]);
                    } elseif ($redirect_path == 'creditmemo' || $redirect_path == 'creditmemo_list') {
                        $this->_redirect('sales/creditmemo/view', ['creditmemo_id' => $shipment_id]);
                    } else {
                        $this->_redirect('infomodus_fedexlabel/items/index');
                    }
                }
            } else {
                $return_url = $url1.'cbsadmin/sales/order';
                $this->messageManager->addSuccess(__("Shipment saved Successfully"));
                $this->_redirect($return_url);
            }

            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t delete item right now. Please review the log and try again.')
            );
            $this->logger->critical($e);
            if ($redirect == 1) {
                $this->_redirect('infomodus_fedexlabel/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            } else {
                $this->_redirect('infomodus_fedexlabel/*/');
            }
            return;
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a item to delete.'));
        $this->_redirect('infomodus_fedexlabel/*/');
    }
}
