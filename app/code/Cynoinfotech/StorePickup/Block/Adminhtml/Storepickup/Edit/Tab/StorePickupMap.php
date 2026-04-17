<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class StorePickupMap extends Generic implements TabInterface
{
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Cynoinfotech\StorePickup\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_encryptor = $encryptor;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    protected function _prepareForm()
    {
        /** @var \Cynoinfotech\StorePickup\Model\StorePickup $storepickup */
        $storepickup = $this->_coreRegistry->registry('storepickup');
        
        $form =  $this->_formFactory->create();
        
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Store Pickup Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        
        $fieldset->addField(
            'store_latitude',
            'text',
            [
                'name' => 'store_latitude',
                'label' => __('Store Latitude'),
                'title' => __('Store Latitude'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_longitude',
            'text',
            [
                'name' => 'store_longitude',
                'label' => __('Store Longitude'),
                'title' => __('Store Longitude'),
                'required' => true,
            ]
        );
        
        $map = $fieldset->addField(
            'map-canvas',
            'text',
            [
                'name'      => 'map-canvas',
            ]
        );
        $map->setRenderer(
            $this->getLayout()->createBlock(
                '\Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Render\Map'
            )
        );
        
        $field_longitude = $form->getElement('store_longitude');
        
        if ($this->dataHelper->getApiKey()) {
            $field_longitude->setAfterElementHtml(
                "<script type=\"text/javascript\" src=\"https://maps.googleapis.com/maps/api/js?key=".
                $this->dataHelper->getApiKey()."&sensor=true\"></script>
                <script type=\"text/javascript\"> 
                    require([\"jquery\",\"adminMap\"],function(jQuery) {
                    
                    });
                    var pathJson = '". $this->getUrl('storepickup/*/*') ."';
                </script>"
            );
        } else {
            $field_longitude->setAfterElementHtml("
                <script type=\"text/javascript\" 
                    src=\"https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&v=3\">
                </script>
        
            <script type=\"text/javascript\">      
                require([\"jquery\",\"adminMap\"],function(jQuery) {  
                    
                });
				var pathJson = '". $this->getUrl('storepickup/*/*') ."';
			</script>");
        }
        
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
        return __('Google Map Informatiom');
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
