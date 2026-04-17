<?php

namespace SchoolZone\Addschool\Block\Adminhtml\Similarproductsattributes\Edit\Tab;

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
    protected $hubcollectionFactory;
    protected $locationcollectionFactory;
    protected $schoolcodecollectionFactory;
    protected $storepickupFactory;

    /**
     * @var \Retailinsights\Postcode\Model\Status
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
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \SchoolZone\Addschool\Model\Status $status,
        \Magento\Eav\Model\Attribute $eavAttribute,
	\Centralbooks\SchoolHub\Model\ResourceModel\Schoolhub\CollectionFactory $hubcollectionFactory,
	\Centralbooks\SchoolCode\Model\ResourceModel\Schoolcode\CollectionFactory $schoolcodecollectionFactory,
	\Centralbooks\LocationCode\Model\ResourceModel\Locationcode\CollectionFactory $locationcollectionFactory,
	\Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->_systemStore = $systemStore;
        $this->_status = $status;
	$this->hubCollection = $hubcollectionFactory;
	$this->locationCollection = $locationcollectionFactory;
	$this->schoolcodeCollection = $schoolcodecollectionFactory;
	$this->storepickup = $storepickupFactory;
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
        /* @var $model \Retailinsights\Postcode\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('similarproductsattributes');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $action = $fieldset->addField(
            "school_type",
            "select",
            [
                "label"     =>      __("School Type"),
                "class"     =>      "required-entry",
                "name"      =>      "school_type",
                "values"    =>      [
                    ["value" => 1,"label" => __("No Validation Required")],
                    ["value" => 2,"label" => __("User ID and Password Validation required")],
                    ["value" => 3,"label" => __("Admission Number validation required")],
                ]

            ]
        );

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

        $school_board = $fieldset->addField(
            "school_board",
            "select",
            [
                "label"     =>      __("school board"),
                "class"     =>      "required-entry",
                "name"      =>      "school_board",
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                'values' => $this->getBoardOption()
            ]
        );
        $school_city = $fieldset->addField(
            "school_city",
            "select",
            [
                "label"     =>      __("school city"),
                "class"     =>      "required-entry",
                "name"      =>      "school_city",
                // "values"    =>      [
                //     ["value" => 0,"label" => __("Do Nothing")],
                //     ["value" => 1,"label" => __("Display Field")],
                // ]
                'values' => $this->getCityOption()
            ]
        );

		$schoolhub = $fieldset->addField(
            "add_schoolhub",
            "select",
            [
                "label"     =>      __("Add School Hub"),
                "class"     =>      "required-entry",
                "name"      =>      "add_schoolhub",
               // "values"    =>      [
                 //   ["value" => 1,"label" => __("Nacharam")],
                 //   ["value" => 0,"label" => __("Ameerpet")],
                //]
				'values' => $this->getHubList()
            ]
        );

	    $school_location = $fieldset->addField(
            "location_code",
            "select",
            [
                "label"     =>      __("Enter Location Code"),
                "class"     =>      "required-entry",
                "name"      =>      "location_code",
               // "values"    =>      [
                 //   ["value" => 1,"label" => __("Nacharam")],
                 //   ["value" => 0,"label" => __("Ameerpet")],
                //]
				'values' => $this->getLocationList()
            ]
        );

        /*$school_location = $fieldset->addField(
            "location_code",
            "text",
            [
                "label"     => __("Enter Location Code"),
                "name"      => "location_code"
            ]
        );*/


        $shipping_charge = $fieldset->addField(
            "shipping_charge",
            "text",
            [
                "label"     => __("Enter Shipping Charges"),
                "name"      => "shipping_charge"
            ]
        );
        
		$shipping_charge = $fieldset->addField(
            "handling_fee",
            "text",
            [
                "label"     => __("Handling Fee"),
                "name"      => "handling_fee"
            ]
        );
		

        /*$shipping_charge = $fieldset->addField(
            "school_code",
            "text",
            [
                "label"     => __("Enter School Code"),
                "name"      => "school_code"
            ]
        );*/

		$school_code = $fieldset->addField(
            "school_code",
            "select",
            [
                "label"     =>      __("Enter School Code"),
                "class"     =>      "required-entry",
                "name"      =>      "school_code",
               // "values"    =>      [
                 //   ["value" => 1,"label" => __("Nacharam")],
                 //   ["value" => 0,"label" => __("Ameerpet")],
                //]
				'values' => $this->getSchoolcodeList()
            ]
        );

        $shipping_charge = $fieldset->addField(
            "school_logo",
            "text",
            [
                "label"     => __("Enter School Image"),
                "name"      => "school_logo"
            ]
        );


        $payu = $fieldset->addField(
            "enable_payu",
            "select",
            [
                "label"     =>      __("Enable PayU"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_payu",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );

        $cashfree = $fieldset->addField(
            "enable_cashfree",
            "select",
            [
                "label"     =>      __("Enable Cashfree"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_cashfree",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );

        $ccavenue = $fieldset->addField(
            "enable_ccavenue",
            "select",
            [
                "label"     =>      __("Enable Ccavenue"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_ccavenue",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );

        $ccavenue = $fieldset->addField(
            "enable_roll",
            "select",
            [
                "label"     =>      __("Enable Rollnumber validation"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_roll",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );

		$storepickup = $fieldset->addField(
            "enable_storepickup",
            "select",
            [
                "label"     =>      __("Enable Store Pickup"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_storepickup",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );

        $fieldset->addField(
            "storepickup_timings",
            "text",
            [
                "label"     => __("Store Pickup Timings"),
                "name"      => "storepickup_timings"
            ]
	);

	$fieldset->addField(
            "pickup_stores",
            "select",
            [
                "label"     =>      __("Pickup Stores"),
                "name"      =>      "pickup_stores",
                "values" => $this->getPickupstore()
            ]
        );

	    $fieldset->addField(
			"willbegiven",
			"text",
			[
				"label"     => __("Will Be Given Msg"),
				"name"      => "willbegiven"
			]
		);

        $fieldset->addField(
			"schoolgiven",
			"text",
			[
				"label"     => __("School Given Msg"),
				"name"      => "schoolgiven"
			]
		);

		$fieldset->addField(
			"school_email",
			"text",
			[
				"label"     => __("School Email"),
				"name"      => "school_email"
			]
		);

			// WYSIWYG Editor
        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('description'),
                'title' => __('description'),
                'rows' => '5',
                'cols' => '5',
                'wysiwyg' => true,
                // 'required' => true,
                'config' => $this->wysiwygConfig->getConfig()
            ]
        );
        $prebooking = $fieldset->addField(
            "enable_prebooking",
            "select",
            [
                "label"     =>      __("Enable Prebooking"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_prebooking",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );

        /*$fieldset->addField(
            'prebooking_description',
            'editor',
            [
                'name' => 'prebooking_description',
                'label' => __('Pre Booking Order Success Message'),
                'title' => __('Pre Booking Order Success Message'),
                'rows' => '5',
                'cols' => '5',
                'wysiwyg' => true,
                'config' => $this->wysiwygConfig->getConfig()
            ]
        );*/

        $preview = $fieldset->addField(
            "enable_preview",
            "select",
            [
                "label"     =>      __("Enable School Preview"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_preview",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );

        $fieldset->addField(
            'preview_description',
            'editor',
            [
                'name' => 'preview_description',
                'label' => __('Preview Description'),
                'title' => __('Preview Description'),
                'rows' => '5',
                'cols' => '5',
                'wysiwyg' => true,
                'config' => $this->wysiwygConfig->getConfig()
            ]
        );
        $preview = $fieldset->addField(
            "enable_cod",
            "select",
            [
                "label"     =>      __("Enable COD"),
                "class"     =>      "required-entry",
                "name"      =>      "enable_cod",
                "values"    =>      [
                    ["value" => 1,"label" => __("Yes")],
                    ["value" => 0,"label" => __("No")],
                ]
            ]
        );


		  $preview = $fieldset->addField(
            "school_status",
            "select",
            [
                "label"     =>      __("School status"),
                "class"     =>      "required-entry",
                "name"      =>      "school_status",
                "values"    =>      [
                    ["value" => 1,"label" => __("Enabled")],
                    ["value" => 0,"label" => __("Disabled")],
                ]
            ]
        );

		$preview = $fieldset->addField(
		"display_bookstore",
		"select",
		[
			"label"     =>      __("Display Bookstore"),
			"class"     =>      "required-entry",
			"name"      =>      "display_bookstore",
			"values"    =>      [
				["value" => 1,"label" => __("Yes")],
				["value" => 0,"label" => __("No")],
			]
		]
		);

	$preview = $fieldset->addField(
                "school_delivery",
                "select",
                [
                        "label"     =>      __("School Delivery"),
                        "class"     =>      "required-entry",
                        "name"      =>      "school_delivery",
                        "values"    =>      [
                                ["value" => 1,"label" => __("Yes")],
                                ["value" => 0,"label" => __("No")],
                        ]
                ]
	);

	$preview = $fieldset->addField(
                "hybrid_delivery",
                "select",
                [
                        "label"     =>      __("Hybrid Delivery"),
                        "class"     =>      "required-entry",
                        "name"      =>      "hybrid_delivery",
                        "values"    =>      [
                                ["value" => 1,"label" => __("Yes")],
                                ["value" => 0,"label" => __("No")],
                        ]
                ]
        );

		$pickupRegion = $fieldset->addField(
			"pickup_region",
			"select",
			[
				"label"  => __("Amazon Pickup Region"),
				"class"  => "required-entry",
				"name"   => "pickup_region",
				"values" => [
					["value" => 1, "label" => __("Telangana")],
					["value" => 2, "label" => __("Mumbai")]
				]
			]
		);

		$pickupRegion = $fieldset->addField(
			"delhivery_pickup_region",
			"select",
			[
				"label"  => __("Delhivery Pickup Region"),
				"class"  => "required-entry",
				"name"   => "delhivery_pickup_region",
				"values" => [
					["value" => 1, "label" => __("Telangana")],
					["value" => 2, "label" => __("Mumbai")]
				]
			]
		);

		$pickupRegion = $fieldset->addField(
			"dtdc_pickup_region",
			"select",
			[
				"label"  => __("DTDC Pickup Region"),
				"class"  => "required-entry",
				"name"   => "dtdc_pickup_region",
				"values" => [
					["value" => 1, "label" => __("Telangana")],
					["value" => 2, "label" => __("Mumbai")]
				]
			]
		);


        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($action->getHtmlId(), $action->getName())
            // ->addFieldMap($anotherField->getHtmlId(), $anotherField->getName())
            // ->addFieldDependence($anotherField->getName(), $action->getName(), 1)

            ->addFieldMap($school_name->getHtmlId(), $school_name->getName())
            ->addFieldMap($school_board->getHtmlId(), $school_board->getName())
            ->addFieldMap($school_city->getHtmlId(), $school_city->getName())

            // ->addFieldMap($username->getHtmlId(), $username->getName())
            // ->addFieldMap($password->getHtmlId(), $password->getName())

            // ->addFieldDependence($username->getName(), $action->getName(), 2)
            // // ->addFieldDependence($password->getName(), $action->getName(), 2)
         


         
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
        $attributeCollection = $this->eavAttribute->getCollection();

    $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
    $options = $attribute->getSource()->getAllOptions();

        $arr = array();
         foreach ($options as  $value) {
             $arr[]=['value' => $value['value'], 'label' => $value['label']];
             // $arr[]=$value['value'];
         }
        return $arr;
    }
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

	public function getHubList(){
        $options[] = ['label' => '', 'value' => ''];
		$hub_list_collection = $this->hubCollection->create();
        $hublistcoll = $hub_list_collection->getData();
       
        foreach ($hublistcoll as $collvalue) {
              $options[] = [
                'value' => $collvalue['schoolhub_id'],
                'label' => $collvalue['schoolhub_name'],
               ];
        }
        return  $options;
		
    }

	public function getLocationList(){
        $options[] = ['label' => '', 'value' => ''];
		$location_list_collection = $this->locationCollection->create();
        $location_listcoll = $location_list_collection->getData();
		//echo '<pre>';print_r($location_listcoll); die;
       
        foreach ($location_listcoll as $collvalue) {
              $options[] = [
                'value' => $collvalue['location_code'],
                'label' => $collvalue['location_name'],
               ];
        }
        return  $options;
		
    }

	public function getSchoolcodeList(){
        $options[] = ['label' => '', 'value' => ''];
		$schoolcode_list_collection = $this->schoolcodeCollection->create();
        $schoolcodelistcoll = $schoolcode_list_collection->getData();
       
        foreach ($schoolcodelistcoll as $collvalue) {
              $options[] = [
                'value' => $collvalue['school_code'],
                'label' => $collvalue['school_name'],
               ];
        }
        return  $options;
		
    }
    public function getTypeOption(){
        $data_array=array(); 
            $data_array[0]='Type 1';
            $data_array[1]='Type 2';
            $data_array[2]='Type 3';
            $data_array[3]='Type 4';
        return($data_array);
    }

    public function getPickupstore(){
       $storepickupcollm = array();
       $options[] = ['label' => '-- Please Select --', 'value' => ''];
       $storepickup = $this->storepickup->create();
       $storepickupcoll = $storepickup->getCollection();
       foreach ($storepickupcoll as $collvalue) {
              $options[] = [
                'value' => $collvalue['entity_id'],
                'label' => $collvalue['name'],
               ];
       }
       return  $options;
    }
}
?>
