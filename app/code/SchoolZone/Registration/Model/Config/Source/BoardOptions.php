<?php
 
namespace SchoolZone\Registration\Model\Config\Source;
  
 class BoardOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
            ['label' => __('Action & Adventure Books'), 'value'=>'1'],
            ['label' => __('Activity Books'), 'value'=>'2'],
            ['label' => __('Age 6-8'), 'value'=>'3'],
            ['label' => __('Ages 0-2'), 'value'=>'4'],
            ['label' => __('Animal Stories'), 'value'=>'5'],
            ['label' => __('Best Sellers'), 'value'=>'6'],
            ['label' => __('Biography'), 'value'=>'7'],
            ['label' => __('CBSE'), 'value'=>'8'],
            ['label' => __('ICSE'), 'value'=>'9'],
            ['label' => __('AP Board'), 'value'=>'10'],
            ['label' => __('ISC'), 'value'=>'11'],
            ['label' => __('Story Books'), 'value'=>'12'],
            ['label' => __('Stories & Novels'), 'value'=>'13'],
            ['label' => __('State Board'), 'value'=>'14'],
            ['label' => __('Science'), 'value'=>'15'],
            ['label' => __('Olymapiad'), 'value'=>'16'],
            ['label' => __('Non Fiction'), 'value'=>'17'],
            ['label' => __('Myths And Fairytale Books'), 'value'=>'18'],
            ['label' => __('Quiz Books'), 'value'=>'19'],
            ['label' => __('Reasoning'), 'value'=>'20']
        ];
  
     return $this->_options;
  
    }
 }