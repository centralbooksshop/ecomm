<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Import\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
     /**
      * constructor
      *
      * @return void
      */
    protected function _construct()
    {
        $this->setId('import_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Csv Information'));
       
        parent::_construct();
    }
}
