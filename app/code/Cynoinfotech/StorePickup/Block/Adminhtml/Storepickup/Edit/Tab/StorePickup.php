<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class StorePickup extends Generic implements TabInterface
{

    
    protected $booleanOption;
    
    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $booleanOption,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\Config\Source\Country $countryFactory,
        array $data = []
    ) {
        $this->booleanOption = $booleanOption;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    protected function _prepareForm()
    {
        /** @var \Cynoinfotech\StorePickup\Model\StorePickup $storepickup */
        $storepickup = $this->_coreRegistry->registry('storepickup');
        $optionsc = $this->_countryFactory->toOptionArray();
        $form =  $this->_formFactory->create();
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Store Pickup Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        
        $fieldset->addType('file', 'Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Helper\File');
        if ($storepickup->getId()) {
            $fieldset->addField(
                'entity_id',
                'hidden',
                ['name' => 'entity_id']
            );
        }
        
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_address',
            'text',
            [
                'name' => 'store_address',
                'label' => __('Store Address'),
                'title' => __('Store Address'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'store_city',
            'text',
            [
                'name' => 'store_city',
                'label' => __('Store City'),
                'title' => __('Store City'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_state',
            'text',
            [
                'name' => 'store_state',
                'label' => __('Store State'),
                'title' => __('Store State'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_country',
            'select',
            [
                'name' => 'store_country',
                'label' => __('Store Country'),
                'title' => __('Store Country'),
                'required' => true,
                'values' => $optionsc,
                
            ]
        );
        
        $fieldset->addField(
            'store_pincode',
            'text',
            [
                'name' => 'store_pincode',
                'label' => __('Store Pincode'),
                'title' => __('Store Pincode'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_phone',
            'text',
            [
                'name' => 'store_phone',
                'label' => __('Store Phone'),
                'title' => __('Store Phone'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_email',
            'text',
            [
                'name' => 'store_email',
                'label' => __('Store Email'),
                'title' => __('Store Email'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_image',
            'file',
            [
                'name' => 'store_image',
                'label' => __('Store Image'),
                'title' => __('Store Image'),
            ]
        );
        
        $fieldset->addField(
            'store_status',
            'select',
            [
                'name'  => 'store_status',
                'label' => __('Store Status'),
                'title' => __('Store Status'),
                'values' => $this->booleanOption->toOptionArray(),
            ]
        );
        
        $storepickupdata = $this->_session->getData('storepickup', true);
        
        if ($storepickupdata) {
            $storepickup->addData($postData);
        } else {
            if (!$storepickup->getId()) {
                $storepickup->addData($storepickup->getDefaultValues());
            }
        }
        
        $form->addValues($storepickup->getData());
        
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    public function getTabLabel()
    {
        return __('General Information');
    }
    
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }
    
    public function canShowTab()
    {
        return true;
    }
    
    public function isHidden()
    {
        return false;
    }
}
