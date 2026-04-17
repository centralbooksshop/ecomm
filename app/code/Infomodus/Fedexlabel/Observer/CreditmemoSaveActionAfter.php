<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Fedexlabel\Observer;

use Magento\Framework\Event\ObserverInterface;

class CreditmemoSaveActionAfter implements ObserverInterface
{
    protected $_coreRegistry;
    protected $_context;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_coreRegistry = $registry;
        $this->_context = $context;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry('fedexlabel_order_id') !== null) {
            $order_id = $this->_coreRegistry->registry('fedexlabel_order_id');
            $shipment_id = $this->_coreRegistry->registry('fedexlabel_shipment_id');
            $paramShipment = $observer->getEvent()->getData('request')->getParam('creditmemo', null);
            if ($paramShipment !== null
                && isset($paramShipment['infomodus_fedex_label'])
                && $paramShipment['infomodus_fedex_label'] == 1
            ) {
                return $this->_context->getResponse()
                    ->setRedirect($this->_context->getUrl()->getUrl('infomodus_fedexlabel/items/edit',
                        [
                            'order_id' => $order_id, 'shipment_id' => $shipment_id,
                            'direction' => 'refund', 'redirect_path' => 'creditmemo'
                        ]))->sendResponse();
            }
        }
        return $this;
    }
}
