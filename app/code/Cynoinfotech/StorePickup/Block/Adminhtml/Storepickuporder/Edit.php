<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Storepickuporder;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    
    protected $coreRegistry =null;
    
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    
    protected function _construct()
    {
        $this->_objectId ='entity_id';
        $this->_controller = 'adminhtml_storepickuporder';
        $this->_blockGroup = 'Cynoinfotech_StorePickup';
        
        parent::_construct();
 
        $this->buttonList->update('save', 'label', __('Save'));
        // $this->buttonList->add(
        //     'saveandcontinue',
        //     [
        //         'label' => __('Save and Continue Edit'),
        //         'class' => 'save',
        //         'data_attribute' => [
        //             'mage-init' => [
        //                 'button' => [
        //                     'event' => 'saveAndContinueEdit',
        //                     'target' => '#edit_order_form'
        //                 ]
        //             ]
        //         ]
        //     ],
        //     -100
        // );
        // $this->buttonList->update('delete', 'label', __('Delete'));
    }
    
    public function getHeaderText()
    {
        $storepickupRegistry = $this->coreRegistry->registry('storepickuporder');
       
        if ($storepickupRegistry->getId()) {
            $storepickuple = $this->escapeHtml($storepickupRegistry->getTitle());
            return __("Edit Store '%1'", $storepickupTitle);
        } else {
            return __("Add Store Order");
        }
    }
 
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };
        ";

        return parent::_prepareLayout();
    }
}
