<?php
 
namespace SchoolZone\Registration\Model\Config\Source;
  
 class CityOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
 {

     /**
     * Get all options
     *
     * @return array
     */
     public function getAllOptions()
     {
        $this->_options = [
            ['label' => __('--select--'), 'value'=>' '],
            ['label' => __('Bangalore'), 'value'=>'1'],
            ['label' => __('Mysore'), 'value'=>'2'],
            ['label' => __('Mumbai'), 'value'=>'3'],
            ['label' => __('Thane'), 'value'=>'4'],
            ['label' => __('Goa'), 'value'=>'5'],
            ['label' => __('Tispur'), 'value'=>'6']
        ];
  
     return $this->_options;
  
    }
 }