<?php
namespace SchoolZone\Search\Block\Adminhtml\Order\Invoice;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    /**
     * Override _initTotals to change the shipping label
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals(); // Retain the parent logic for invoice totals

        // Check if the shipping total exists and modify its label
        if (isset($this->_totals['shipping'])) {
            // Change the label of shipping & handling
           $this->_totals['shipping']->setLabel(__('Shipping & Handling(Incl. GST)'));
        }

        return $this;
 } 
}

