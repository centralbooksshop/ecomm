<?php
 
namespace SchoolZone\Registration\Model\Config\Source;
  
 class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
 {
    // protected $eavAttribute;
    private $collectionFactory;
    public function __construct(
        \SchoolZone\Addschool\Model\SimilarproductsattributesFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

     /**
     * Get all options
     *
     * @return array
     */
     public function getAllOptions()
     {
        $options = $collection = $this->getOptionArrayBoard();
        $arr = array();
        array_push($arr, ['label' => __('--select--'), 'value' => '']);
        foreach($options as $key => $value){
            array_push($arr, ['label' => __($value), 'value' => $key]);
        }
        $this->_options = $arr;
  
     return $this->_options;
  
    }
    
    public function getOptionArrayBoard()
    {
        
        $arr = array();
        $schoolCollectionNew = $this->collectionFactory->create(); 
        $filter = $schoolCollectionNew->getCollection();
            // ->addFieldToFilter('school_name', $catData['school_name']);
            // ->addFieldToFilter('school_board', $catData['board'])
            // ->addFieldToFilter('school_city', $catData['cities']);

        $schoolFilterData =  $filter->getData();
        foreach ($schoolFilterData as $school) {  
            $arr[$school['school_name']]=  $school['school_name_text'];
        }


        // $attributeCollection = $this->eavAttribute->getCollection();

        // $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name_text');
        // $options = $attribute->getSource()->getAllOptions();

        //         foreach ($options as  $value) {
        //             $arr[$value['value']]=  $value['label'];
        //         }
            return $arr;
    }
 }