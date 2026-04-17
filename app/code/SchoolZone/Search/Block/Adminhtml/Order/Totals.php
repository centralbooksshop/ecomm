<?php
namespace SchoolZone\Search\Block\Adminhtml\Order;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    /**
     * Override _initTotals to change the shipping label
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals(); // Retain the parent logic for order totals

        $order = $this->getSource(); // Get the order source

        // Check if 'shipping' total exists and modify its label
        if (isset($this->_totals['shipping'])) {
            // Modify the shipping label
            $this->_totals['shipping']->setLabel(__('Shipping & Handling(Incl. GST)'));
        }

        return $this;
    }
}
