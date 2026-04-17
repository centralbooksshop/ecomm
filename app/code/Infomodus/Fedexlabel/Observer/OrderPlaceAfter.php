<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Fedexlabel\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderPlaceAfter implements ObserverInterface
{
    protected $_coreRegistry;
    protected $_handy;
    protected $itemsFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Infomodus\Fedexlabel\Helper\Handy $handy,
        \Infomodus\Fedexlabel\Model\ItemsFactory $itemsFactory
    )
    {
        $this->_coreRegistry = $registry;
        $this->_handy = $handy;
        $this->itemsFactory = $itemsFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        if ($this->_handy->_conf
                ->getStoreConfig('fedexlabel/frontend_autocreate_label/frontend_order_autocreate_label_enable', $order->getStoreId()) == 1) {
            $storeId = $order->getStoreId();
            $shippingActiveMethods = trim($this->_handy->_conf->getStoreConfig('fedexlabel/frontend_autocreate_label/apply_to', $storeId), " ,");
            $shippingActiveMethods = strlen($shippingActiveMethods) > 0 ? explode(",", $shippingActiveMethods) : [];
            $orderStatuses = explode(",", trim($this->_handy->_conf->getStoreConfig('fedexlabel/frontend_autocreate_label/orderstatus', $storeId), " ,"));
            if (((
                        isset($shippingActiveMethods)
                        && count($shippingActiveMethods) > 0
                        && in_array($order->getShippingMethod(), $shippingActiveMethods)
                    )
                    || strpos($order->getShippingMethod(), "fedex_") === 0
                )
                && (isset($orderStatuses) && count($orderStatuses) > 0
                    && in_array($order->getStatus(), $orderStatuses))
            ) {
                $label = $this->itemsFactory->create()->getCollection()
                    ->addFieldToFilter('type', 'shipment')
                    ->addFieldToFilter('lstatus', 0)
                    ->addFieldToFilter('order_id', $order->getId());
                if (count($label) == 0) {
                    $shipment = $order->getShipmentsCollection();
                    $shipment_id = null;
                    if (count($shipment) > 0) {
                        $shipment = $shipment->getFirstItem();
                        $shipment_id = $shipment->getId();
                    }

                    $this->_handy->intermediate($order->getId(), 'shipment');
                    $this->_handy->defConfParams['package'] = $this->_handy->defPackageParams;
                    $this->_handy->getLabel(null, 'shipment', $shipment_id, $this->_handy->defConfParams);
                }
            }
        }

        $this->_coreRegistry->unregister('infomodus_fedexlabel_autocreate_label');
        return $this;
    }
}
