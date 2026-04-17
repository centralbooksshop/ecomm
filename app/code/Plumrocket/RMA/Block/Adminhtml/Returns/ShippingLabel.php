<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

class ShippingLabel extends \Plumrocket\RMA\Block\Adminhtml\Returns\Template
{
    /**
     * Check if has shipping label
     *
     * @return bool
     */
    public function hasShippingLabel()
    {
        return (bool)$this->getEntity()->getShippingLabel();
    }

    /**
     * Get shipping label url
     *
     * @return string
     */
    public function getShippingLabelUrl()
    {
        return $this->returnsHelper->getFileUrl(
            $this->getEntity(),
            $this->getEntity()->getShippingLabel(),
            true
        );
    }

    /**
     * Get checkbox element of delete field
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getCheckboxOfDelete()
    {
        return $this->createElement('shipping_label_delete', 'checkbox', [
            'name'      => 'shipping_label_delete',
            'label'     => __('Delete'),
            'value'     => '1',
            'checked'   => $this->dataHelper->getFormData('shipping_label_delete'),
            'class'     => 'admin__control-checkbox',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getEntity()->isVirtual()) {
            return '';
        }

        return parent::_toHtml();
    }
}
