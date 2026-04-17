<?php
namespace SchoolZone\Addschool\Block\Adminhtml\Similarproductsattributes;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $eavAttribute;
     protected $eavConfig;

    /**
     * @var \SchoolZone\Addschool\Model\similarproductsattributesFactory
     */
    protected $_similarproductsattributesFactory;

    /**
     * @var \Retailinsights\Postcode\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \SchoolZone\Addschool\Model\similarproductsattributesFactory $similarproductsattributesFactory
     * @param \SchoolZone\Addschoole\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \SchoolZone\Addschool\Model\SimilarproductsattributesFactory $SimilarproductsattributesFactory,
        \SchoolZone\Addschool\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Attribute $eavAttribute,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->_similarproductsattributesFactory = $SimilarproductsattributesFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_similarproductsattributesFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
						$this->addColumn(
						'id',
						[
							'header' => __('ID'),
							'type' => 'number',
							'index' => 'id',
							'header_css_class' => 'col-id',
							'column_css_class' => 'col-id'
						]
						);

						$this->addColumn(
                            'school_name',
                            [
                                'header' => __('School ID'),
                                'index' => 'school_name',
                                'type' => 'text'
                            ]
                        );
						
						$this->addColumn(
                            'school_type',
                            [
                                'header' => __('school type'),
                                'index' => 'school_type',
                                'type' => 'options',
                                'options' => array('1' => __('No Validation Required'),'2' => __('User ID and Password Validation required'),'3' => __('Admission Number validation required'))
                                // 'options' => \SchoolZone\Addschool\Block\Adminhtml\Similarproductsattributes\Edit\Tab::getOptionArray2()
                            ]
                        );

                        $this->addColumn(
                            'school_name_text',
                            [
                                'header' => __('school Name Text'),
                                'index' => 'school_name_text',
                                'type' => 'text'
                            ]
                        );
                        $this->addColumn(
                            'school_board',
                            [
                                'header' => __('school board'),
                                'index' => 'school_board',
                                'type' => 'options',
                                 // 'options' => array('1' => 'Yes', '0' => 'No')
                               'options' => $this->getOptionArrayBoard()
                            ]
                        );
                        $this->addColumn(
                            'school_city',
                            [
                                'header' => __('school city'),
                                'index' => 'school_city',
                                'type' => 'options',
                                 // 'options' => array('1' => 'Yes', '0' => 'No')
                                'options' => $this->getOptionArrayCity()
                            ]
                        );
                        /*$this->addColumn(
                            'description',
                            [
                                'header' => __('Description'),
                                'index' => 'description',
                                'type' => 'text',
                                 // 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
                            ]
                        );*/
                        $this->addColumn(
                            'shipping_charge',
                            [
                                'header' => __('Shipping Charge'),
                                'index' => 'shipping_charge',
                                'type' => 'text'
                            ]
                        );

						$this->addColumn(
                            'handling_fee',
                            [
                                'header' => __('Handling Fee'),
                                'index' => 'handling_fee',
                                'type' => 'text'
                            ]
                        );

                        $this->addColumn(
                            'location_code',
                            [
                                'header' => __('Location Code'),
                                'index' => 'location_code',
                                'type' => 'text'
                            ]
                        );

						$this->addColumn(
                            'school_code',
                            [
                                'header' => __('School Code'),
                                'index' => 'school_code',
                                'type' => 'text'
                            ]
                        );
                        $this->addColumn(
                            'enable_payu',
                            [
                                'header' => __('Enabled PayU'),
                                'index' => 'enable_payu',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
                            ]
                        );
                        $this->addColumn(
                            'enable_cashfree',
                            [
                                'header' => __('Enabled Cashfree'),
                                'index' => 'enable_cashfree',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                            ]
                        );
                        $this->addColumn(
                            'enable_ccavenue',
                            [
                                'header' => __('Enabled Ccavenue'),
                                'index' => 'enable_ccavenue',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                            ]
                        );
                        $this->addColumn(
                            'enable_prebooking',
                            [
                                'header' => __('Enable Prebooking'),
                                'index' => 'enable_prebooking',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                            ]
                        );

                       /* $this->addColumn(
                            'prebooking_description',
                            [
                                'header' => __('Pre Booking Order Success Message'),
                                'index' => 'prebooking_description',
                                'type' => 'text'
                            ]
                        ); */

                        $this->addColumn(
                            'enable_preview',
                            [
                                'header' => __('Enable School Preview'),
                                'index' => 'enable_preview',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                            ]
                        );
                        /*$this->addColumn(
                            'preview_description',
                            [
                                'header' => __('Preview Description'),
                                'index' => 'preview_description',
                                'type' => 'text'
                            ]
                        );*/
                        $this->addColumn(
                            'enable_cod',
                            [
                                'header' => __('Enable COD'),
                                'index' => 'enable_cod',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                            ]
                        );

						$this->addColumn(
                            'school_status',
                            [
                                'header' => __('School status'),
                                'index' => 'school_status',
                                'type' => 'options',
                                 'options' => array('1' => 'Enabled', '0' => 'Disabled')
                            ]
                        );

						$this->addColumn(
                            'display_bookstore',
                            [
                                'header' => __('Display Bookstore'),
                                'index' => 'display_bookstore',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                            ]
                        );

						$this->addColumn(
                            'willbegiven',
                            [
                                'header' => __('Will Be Given Msg'),
                                'index' => 'willbegiven',
                                'type' => 'text'
                            ]
                        );

						$this->addColumn(
							'schoolgiven',
							[
								'header' => __('School Given Msg'),
								'index' => 'schoolgiven',
								'type' => 'text'
							]
						);

						$this->addColumn(
							'school_email',
							[
								'header' => __('School Email'),
								'index' => 'school_email',
								'type' => 'text'
							]
						);


					    $this->addColumn(
							'pickup_region',
							[
								'header'  => __('Amazon Pickup Region'),
								'index'   => 'pickup_region',
								'type'    => 'options',
								'options' => [
									1 => __('Telangana'),
									2 => __('Mumbai')
								]
							]
						);

						$this->addColumn(
							'delhivery_pickup_region',
							[
								'header'  => __('Delhivery Pickup Region'),
								'index'   => 'delhivery_pickup_region',
								'type'    => 'options',
								'options' => [
									1 => __('Telangana'),
									2 => __('Mumbai')
								]
							]
						);

						$this->addColumn(
							'dtdc_pickup_region',
							[
								'header'  => __('DTDC Pickup Region'),
								'index'   => 'dtdc_pickup_region',
								'type'    => 'options',
								'options' => [
									1 => __('Telangana'),
									2 => __('Mumbai')
								]
							]
						);



        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('RetailInsights_SimilarProducts::similarproductsattributes/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('similarproductsattributes');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('addschool/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'school_status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('addschool/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'school_status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('addschool/*/index', ['_current' => true]);
    }

    /**
     * @param \Retailinsights\Postcode\Model\similarproductsattributes|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'addschool/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray2()
		{
            $data_array=array(); 
			$data_array[0]='No';
			$data_array[1]='Yes';
            return($data_array);
		}

        public function getOptionArrayBoard()
        {

            $attributeCollection = $this->eavAttribute->getCollection();

            $attribute = $this->eavConfig->getAttribute('catalog_product', 'board');
            $options = $attribute->getSource()->getAllOptions();

                $arr = array();
                 foreach ($options as  $value) {
                     $arr[$value['value']]=  $value['label'];
                 }
                return $arr;
        }

        public function getOptionArrayCity()
        {

            $attributeCollection = $this->eavAttribute->getCollection();

            $attribute = $this->eavConfig->getAttribute('catalog_product', 'cities');
            $options = $attribute->getSource()->getAllOptions();

                $arr = array();
                 foreach ($options as  $value) {
                     $arr[$value['value']]=  $value['label'];
                 }
                return $arr;
        }

        public function getOptionArray3()
        {
            $attributeCollection = $this->eavAttribute->getCollection();

            $data_array=array(); 
            $data_array[2]='No';
            $data_array[4]='Yes';
            return($data_array);
        }
		
        static public function getOptionArrayName(){
        }

}