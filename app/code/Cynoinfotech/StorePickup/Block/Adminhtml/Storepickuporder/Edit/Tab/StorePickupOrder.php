<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Storepickuporder\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class StorePickupOrder extends Generic implements TabInterface
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
        /** @var \Cynoinfotech\StorePickup\Model\StorePickupOrder $storepickuporder */
        $storepickuporder = $this->_coreRegistry->registry('storepickuporder');
        $optionsc = $this->_countryFactory->toOptionArray();
        $form =  $this->_formFactory->create();
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Store Pickup Order Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        
        // $fieldset->addType('file', 'Cynoinfotech\StorePickup\Block\Adminhtml\Storepickuporder\Helper\File');
        if ($storepickuporder->getId()) {
            $fieldset->addField(
                'entity_id',
                'hidden',
                ['name' => 'entity_id']
            );
        }
        
        $fieldset->addField(
            'increment_id',
            'text',
            [
                'name' => 'increment_id',
                'label' => __('Increment Id'),
                'title' => __('Increment Id'),
			    'readonly'  => true,
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'store_name',
            'text',
            [
                'name' => 'store_name',
                'label' => __('Store Name'),
                'title' => __('Store Name'),
			    'readonly'  => true,
                'required' => true,
            ]
        );
        $fieldset->addField(
            'pickup_person_name',
            'text',
            [
                'name' => 'pickup_person_name',
                'label' => __('Pickup person name'),
                'title' => __('Pickup person name'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'pickup_person_id',
            'text',
            [
                'name' => 'pickup_person_id',
                'label' => __('Pickup person Id'),
                'title' => __('Pickup person Id'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'customer_phone',
            'text',
            [
                'name' => 'customer_phone',
                'label' => __('Customer Phone'),
                'title' => __('Customer Phone'),
			     'readonly'  => true,
                'required' => true,
                
            ]
        );
        
        $fieldset->addField(
            'payment_method',
            'text',
            [
                'name' => 'payment_method',
                'label' => __('Payment Method'),
                'title' => __('Payment Method'),
			    'readonly'  => true,
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'given_person',
            'text',
            [
                'name' => 'given_person',
                'label' => __('Given Person'),
                'title' => __('Given Person'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'calendar_inputField',
            'date',
            [
                'name' => 'calendar_inputField',
                'label' => __('Pickup Date'),
                'title' => __('Pickup Date'),
			    'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
				'time_format' => $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT),
                'required' => true,
            ]
        );
        

		$fieldset->addField(
        'delivery_date',
        'date',
			[
				 'name' => 'delivery_date',
				'label' => __('Delivery Date'),
				'title' => __('Delivery Date'),
				/*'date_format' => 'yyyy-MM-dd',
				'time_format' => 'hh:mm:ss'*/
				'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
				'time_format' => $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT),
			]
           );
        
        $fieldset->addField(
            'order_status',
            'select',
            [
                'name'  => 'order_status',
                'label' => __('Order Status'),
                'title' => __('Order Status'),
			    "values"    =>      [
                    ["value" => "processing","label" => __("Processing")],
			        ["value" => "dispatched_to_courier","label" => __("Dispatched To Courier")],
                    ["value" => "order_delivered","label" => __("Delivered")],
			        ["value" => "order_not_delivered","label" => __("Order Not Delivered")],
                ]
            ]
        );

	

        $fieldset->addField(
            'store_status',
            'select',
            [
                'name'  => 'store_status',
                'label' => __('Store Status'),
                'title' => __('Store Status'),
                "values"    =>      [
                    ["value" => "1","label" => __("Delivered")],
                    ["value" => "0","label" => __("Wait For Delivery")],
                ]
            ]
        );
        
        $storepickuporderdata = $this->_session->getData('storepickuporder', true);

        if ($storepickuporderdata) {
            $storepickuporder->addData($postData);
        } else {
            if (!$storepickuporder->getId()) {
                $storepickuporder->addData([]);
            }
        }

        $form->addValues($storepickuporder->getData());
        
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
