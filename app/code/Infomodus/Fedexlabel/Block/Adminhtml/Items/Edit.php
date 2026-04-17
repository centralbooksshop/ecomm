<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Fedexlabel\Block\Adminhtml\Items;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_items';
        $this->_blockGroup = 'Infomodus_Fedexlabel';

        parent::_construct();
        $this->buttonList->update('back', 'label', __('Back to the Labels list'));
        $this->addButton(
            'back_to_order',
            [
                'label' => __('Back to the Order'),
                'class' => __('back'),
                'id' => 'back-to-order-button',
                'onclick' => 'setLocation(\''.$this->getUrl('sales/order/view',
                        ['order_id' => $this->getRequest()->getParam('order_id')]).'\')'
            ]
        );
        if ($this->getRequest()->getParam('shipment_id', null) !== null) {
            if ($this->getRequest()->getParam('direction') != 'refund') {
                $this->addButton(
                    'back_to_shipment',
                    [
                        'label' => __('Back to the Shipment'),
                        'class' => __('back'),
                        'id' => 'back-to-shipment-button',
                        'onclick' => 'setLocation(\''.$this->getUrl('sales/shipment/view',
                                ['shipment_id' => $this->getRequest()->getParam('shipment_id')]).'\')'
                    ]
                );
            }
            if ($this->getRequest()->getParam('direction') == 'refund') {
                $this->addButton(
                    'back_to_creditmemo',
                    [
                        'label' => __('Back to the Creditmemo'),
                        'class' => __('back'),
                        'id' => 'back-to-creditmemo-button',
                        'onclick' => 'setLocation(\''.$this->getUrl('sales/creditmemo/view',
                                ['creditmemo_id' => $this->getRequest()->getParam('shipment_id')]).'\')'
                    ]
                );
            }
        }
    }

    /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Create label');
    }
}
