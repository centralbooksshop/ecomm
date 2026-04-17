<?php

namespace SchoolZone\Registration\Block\Adminhtml\Similarproductsattributes\Edit\Tab;

/**
 * Similarproductsattributes edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $eavAttribute;
    protected $eavConfig;
    protected $wysiwygConfig;
    protected $postFactory;

    /**
     * @var \Retailinsights\Registration\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \SchoolZone\Search\Model\PostFactory $postFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \SchoolZone\Registration\Model\Status $status,
        \Magento\Eav\Model\Attribute $eavAttribute,
        array $data = []
    ) {
        $this->postFactory = $postFactory;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Retailinsights\Registration\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('similarproductsattributes');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }


        $school_name = $fieldset->addField(
            "school_name",
            "select",
            [
                "label"     =>      __("school name"),
                "class"     =>      "required-entry",
                "name"      =>      "school_name",
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                "values" => $this->getOptionArray()
            ]
        );
         $school_name_text = $fieldset->addField(
            "school_name_text",
            "hidden",
            [
                "label"     =>      __("school school_name_text"),
                "class"     =>      "school_name_text",
                "name"      =>      "school_name_text",
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                // "values" => $this->getOptionArray()
            ]
        );
      
                            // $(".school_name_text").val(response.responseText);
         $school_name_text->setAfterElementHtml('<script>
//<![CDATA[
    require(["jquery"], function ($) {
        $(document).ready(function() {


             $("select[name=school_name]").change(function () {

                 var school_name =  $(".required-entry").val();
                    var base_url = document.location.origin+"/schoolzone_search/Index/Search";
                    console.log("changed");
                    console.log(base_url);
                      var param={ 
                            school_name:school_name,
                            type:"school_name_text" 
                        }

                    $.ajax({
                       
                        url: base_url,
                        data: param,
                        type: "POST",
                        dataType: "json",
                        complete:function(response){
                           
                            
                        },
                        error:function(xhr,status,errorThrown){
                        }
                    });

           
                });
            });
    });
    
//]]>
</script>');



        $student_name = $fieldset->addField(
            "student_name",
            "text",
            [
                "label"     =>      __("Student name"),
                "class"     =>      "required-entry",
                "name"      =>      "student_name"
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                // "values" => $this->getOptionArray()
            ]
        );
         $student_name = $fieldset->addField(
        "class",
        "select",
            [
                "label"     =>      __("Student Class"),
                "class"     =>      "required-entry",
                "name"      =>      "class",
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                "values" => $this->getOptionClass()
            ]
        );
        $school_username = $fieldset->addField(
            "username",
            "text",
            [
                "label"     =>      __("Username"),
                "class"     =>      "required-entry",
                "name"      =>      "username"
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                // "values" => $this->getOptionArray()
            ]
        );
        $school_password = $fieldset->addField(
            "password",
            "password",
            [
                "label"     =>      __("Password"),
                "class"     =>      "required-entry",
                "name"      =>      "password"
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                // "values" => $this->getOptionArray()
            ]
        );
        $admission_id = $fieldset->addField(
            "admission_id",
            "text",
            [
               'name' => 'admission_id',
                'label' => __('Admission Id'),
                  'class' => 'validate-number'
            ]
        );

        
        
            // WYSIWYG Editor
        // $fieldset->addField(
        //     'description',
        //     'editor',
        //     [
        //         'name' => 'description',
        //         'label' => __('description'),
        //         'title' => __('description'),
        //         'rows' => '5',
        //         'cols' => '5',
        //         'wysiwyg' => true,
        //         // 'required' => true,
        //         'config' => $this->wysiwygConfig->getConfig()
        //     ]
        // );
        
        
// echo $school_name);
// echo $school_name->getName();

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Form\Element\Dependence')
           
            // ->addFieldMap($anotherField->getHtmlId(), $anotherField->getName())
            // ->addFieldDependence($anotherField->getName(), $action->getName(), 1)

            ->addFieldMap($school_name->getHtmlId(), $school_name->getName())
            ->addFieldMap($school_name_text->getHtmlId(), $school_name_text->getName())
            ->addFieldMap($student_name->getHtmlId(), $student_name->getName())
            ->addFieldMap($school_username->getHtmlId(), $school_username->getName())
            ->addFieldMap($school_password->getHtmlId(), $school_password->getName())
            ->addFieldMap($admission_id->getHtmlId(), $admission_id->getName())
            

            // ->addFieldMap($school_board->getHtmlId(), $school_board->getName())
            // ->addFieldMap($school_city->getHtmlId(), $school_city->getName())

            // ->addFieldMap($username->getHtmlId(), $username->getName())
            // ->addFieldMap($password->getHtmlId(), $password->getName())

            // ->addFieldDependence($admission_id->getName(), $school_name->getName(), 1)
            ->addFieldDependence($school_username->getName(), $school_name->getName(), 2)
            ->addFieldDependence($school_password->getName(), $school_name->getName(), 2)
         


         
        );
        // $this->setChild(
        //     'form_after',
        //     $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Form\Element\Dependence')
        //     ->addFieldMap($action->getHtmlId(), $action->getName())
        //     ->addFieldMap($school_name->getHtmlId(), $school_name->getName())
        //     ->addFieldMap($school_board->getHtmlId(), $school_board->getName())
        //     ->addFieldMap($school_city->getHtmlId(), $school_city->getName())
        //     ->addFieldDependence($anotherField->getName(), $action->getName(), 0)
        // );



						

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
     public function getOptionArray(){
        $collection = $this->postFactory->create()->getCollection();
        $collection->getSelect()->group('school_name_text');

        $arr = array();
        foreach ($collection as $value) {
            if($value['school_type'] !=0){
                 $arr[]=['value' => $value['school_type'], 'label' => $value['school_name_text']];

            }
        }
        return $arr;

    }
     public function getOptionSave(){
            // $collection = $this->postFactory->create()->getCollection();
            // $collection->getSelect()->group('school_name_text');

            // $arr = array();
            // foreach ($collection as $value) {
            //     if($value['school_type'] !=0){
            //          $arr[]=['value' => $value['school_type'], 'label' => $value['school_name_text']];

            //     }
            // }
            // return $arr;

    }


    //     $attributeCollection = $this->eavAttribute->getCollection();

    // $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
    // $options = $attribute->getSource()->getAllOptions();

    //     $arr = array();
    //      foreach ($options as  $value) {
    //          $arr[]=['value' => $value['value'], 'label' => $value['label']];
    //          // $arr[]=$value['value'];
    //      }
    //     return $arr;
    // }
// ***************************************************************************************************************************************
    public function getBoardOption(){
    $attributeCollection = $this->eavAttribute->getCollection();

    $attribute = $this->eavConfig->getAttribute('catalog_product', 'board');
    $options = $attribute->getSource()->getAllOptions();

        $arr = array();
         foreach ($options as  $value) {
             $arr[]=['value' => $value['value'], 'label' => $value['label']];
             
         }
        return $arr;
    }
    public function getOptionClass(){
    $attributeCollection = $this->eavAttribute->getCollection();

    $attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
    $options = $attribute->getSource()->getAllOptions();

        $arr = array();
         foreach ($options as  $value) {
             $arr[]=['value' => $value['value'], 'label' => $value['label']];
             
         }
        return $arr;
    }


    public function getCityOption(){
         $attributeCollection = $this->eavAttribute->getCollection();

    $attribute = $this->eavConfig->getAttribute('catalog_product', 'cities');
    $options = $attribute->getSource()->getAllOptions();

        $arr = array();
         foreach ($options as  $value) {
             $arr[]=['value' => $value['value'], 'label' => $value['label']];
         }
        return $arr;
    }
    public function getTypeOption(){
        $data_array=array(); 
            $data_array[0]='Type 1';
            $data_array[1]='Type 2';
            $data_array[2]='Type 3';
            $data_array[3]='Type 4';
        return($data_array);
    }
}
?>
