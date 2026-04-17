<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Plugin;

class AddToolbarButtons
{
    protected $context;
    /**
     * @var \Infomodus\Fedexlabel\Model\ItemsFactory
     */
    private $items;
    private $shipment;
    private $creditmemo;

    public function __construct(
        \Infomodus\Fedexlabel\Model\ItemsFactory $items,
        \Magento\Sales\Model\Order\Shipment $shipment,
    \Magento\Sales\Model\Order\Creditmemo $creditmemo
    )
    {
        $this->items = $items;
        $this->shipment = $shipment;
        $this->creditmemo = $creditmemo;
    }

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    )
    {
        $this->context = $context;
        if ($context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
            if ($context->getToolbar()->getChildBlock('create_fedex_labels') === false) {
                $context->getToolbar()->addChild(
                    'create_fedex_labels',
                    'Magento\Backend\Block\Widget\Button\SplitButton',
                    [
                        'label' => __('Create FedEx label'),
                        'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                        'button_class' => 'widget-button-save',
                        'options' => $this->getOrderButtonOptions(),
                    ]
                );
            }

            if ($context->getToolbar()->getChildBlock('show_fedex_labels') === false) {
                $labels = $this->items->create()->getCollection()
                    ->addFieldToFilter('order_id', $this->context->getRequest()->getParam('order_id'));
                if (count($labels) > 0) {
                    $buttonList->add(
                        'show_fedex_labels',
                        [
                            'label' => __('Show FedEx label(s)'),
                            'class' => 'primary',
                            'onclick' => 'setLocation("' . $this->context->getUrl('infomodus_fedexlabel/items/show',
                                    [
                                        'order_id' => $this->context->getRequest()->getParam('order_id'),
                                        'redirect_path' => 'order',
                                    ]) . '")',
                        ]
                    );
                }
            }
        }
        if ($context instanceof \Magento\Shipping\Block\Adminhtml\View) {
            if ($context->getToolbar()->getChildBlock('create_fedex_labels2') === false) {
                $shipment = $this->shipment->load($this->context->getRequest()->getParam('shipment_id'));
                $context->getToolbar()->addChild(
                    'create_fedex_labels2',
                    'Magento\Backend\Block\Widget\Button\SplitButton',
                    [
                        'label' => __('Create FedEx label'),
                        'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                        'button_class' => 'widget-button-save',
                        'options' => $this->getOrderButtonOptions(false, $shipment->getOrderId(), 'shipment'),
                    ]
                );
            }

            if ($context->getToolbar()->getChildBlock('show_fedex_labels2') === false) {
                $labels = $this->items->create()->getCollection()
                    ->addFieldToFilter('order_id', $shipment->getOrderId())
                    ->addFieldToFilter('shipment_id', $this->context->getRequest()->getParam('shipment_id'));
                if (count($labels) > 0) {
                    $buttonList->add(
                        'show_fedex_labels2',
                        [
                            'label' => __('Show FedEx label(s)'),
                            'class' => 'primary',
                            'onclick' => 'setLocation("' . $this->context->getUrl('infomodus_fedexlabel/items/show',
                                    [
                                        'order_id' => $shipment->getOrderId(),
                                        'shipment_id' => $this->context->getRequest()->getParam('shipment_id'),
                                        'type' => 'shipment', 'redirect_path' => 'shipment',
                                    ]) . '")',
                        ]
                    );
                }
            }
        }
        if ($context instanceof \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View) {
            if ($context->getToolbar()->getChildBlock('create_fedex_labels3') === false) {
                $shipment = $this->creditmemo->load($this->context->getRequest()->getParam('creditmemo_id'));
                $buttonList->add(
                    'create_fedex_labels3',
                    [
                        'label' => __('Create FedEx label'),
                        'class' => 'primary',
                        'onclick' => 'setLocation("' . $this->context->getUrl('infomodus_fedexlabel/items/edit',
                                [
                                    'order_id' => $shipment->getOrderId(),
                                    'shipment_id' => $this->context->getRequest()->getParam('creditmemo_id'),
                                    'direction' => 'refund', 'redirect_path' => 'creditmemo',
                                ]) . '")',
                    ]
                );
            }

            if ($context->getToolbar()->getChildBlock('show_fedex_labels3') === false) {
                $labels = $this->items->create()->getCollection()
                    ->addFieldToFilter('order_id', $shipment->getOrderId())
                    ->addFieldToFilter('shipment_id', $this->context->getRequest()->getParam('shipment_id'));
                if (count($labels) > 0) {
                    $buttonList->add(
                        'show_fedex_labels3',
                        [
                            'label' => __('Show FedEx label(s)'),
                            'class' => 'primary',
                            'onclick' => 'setLocation("' . $this->context->getUrl('infomodus_fedexlabel/items/show',
                                    [
                                        'order_id' => $shipment->getOrderId(),
                                        'shipment_id' => $this->context->getRequest()->getParam('creditmemo_id'),
                                        'type' => 'creditmemo', 'redirect_path' => 'creditmemo',
                                    ]) . '")',
                        ]
                    );
                }
            }
        }
        return [$context, $buttonList];
    }

    protected function getOrderButtonOptions($isReturn = true, $orderId = null, $redirect_path = 'order')
    {
        $options = [];
        $options[] = [
            'id' => 'create_fedex_label_direct',
            'label' => __('Shipping FedEx label'),
            'onclick' => 'setLocation("' . $this->context->getUrl('infomodus_fedexlabel/items/edit',
                    [
                        'order_id' => $this->context->getRequest()->getParam('order_id', $orderId),
                        'shipment_id' => $this->context->getRequest()->getParam('shipment_id', null),
                        'direction' => 'shipment', 'redirect_path' => $redirect_path,
                    ]) . '")',
            'default' => true,
        ];

        if ($isReturn === true) {
            $options[] = [
                'id' => 'create_fedex_label_return',
                'label' => __('RMA(return) FedEx label'),
                'onclick' => 'setLocation("' . $this->context->getUrl('infomodus_fedexlabel/items/edit',
                        [
                            'order_id' => $this->context->getRequest()->getParam('order_id', $orderId),
                            'direction' => 'refund', 'redirect_path' => $redirect_path,
                        ]) . '")',
            ];
        }

        $options[] = [
            'id' => 'create_fedex_label_invert',
            'label' => __('Invert FedEx label'),
            'onclick' => 'setLocation("' . $this->context->getUrl('infomodus_fedexlabel/items/edit',
                    [
                        'order_id' => $this->context->getRequest()->getParam('order_id', $orderId),
                        'shipment_id' => $this->context->getRequest()->getParam('shipment_id', null),
                        'direction' => 'invert', 'redirect_path' => $redirect_path,
                    ]) . '")',
        ];

        return $options;
    }
}
