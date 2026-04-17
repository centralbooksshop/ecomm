<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Storepickuporder\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * constructor
     *
     * return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('storepickuporder_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle('Stores Order Information');
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('storepickuporder_info', [
            'label' => __('General Information'),
            'title' => __('General Information'),
            'content' => $this->getLayout()->createBlock(
                'Cynoinfotech\StorePickup\Block\Adminhtml\Storepickuporder\Edit\Tab\StorePickupOrder'
            )->toHtml(),
        ]);
        
            return parent::_beforeToHtml();
    }
}
