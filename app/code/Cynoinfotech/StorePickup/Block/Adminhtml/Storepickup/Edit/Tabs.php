<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Edit;

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
        $this->setId('storepickup_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle('Stores Information');
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('storepickup_info', [
            'label' => __('General Information'),
            'title' => __('General Information'),
            'content' => $this->getLayout()->createBlock(
                'Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Edit\Tab\StorePickup'
            )->toHtml(),
        ]);
        
            $this->addTab('storepickup_map_info', [
            'label' => __('Google Map Information'),
            'title' => __('Google Map Information'),
            'content' => $this->getLayout()->createBlock(
                'Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Edit\Tab\StorePickupMap'
            )->toHtml(),
            ]);
            return parent::_beforeToHtml();
    }
}
