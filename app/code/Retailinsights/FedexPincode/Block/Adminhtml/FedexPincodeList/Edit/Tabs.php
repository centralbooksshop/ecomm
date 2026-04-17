<?php
namespace Retailinsights\FedexPincode\Block\Adminhtml\FedexPincodeList\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('fedexpincodelist_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('FedEx Pincode Information'));
    }
}