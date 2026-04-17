<?php
namespace SchoolZone\Registration\Block\Adminhtml\Similarproductsattributes;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $eavAttribute;
     protected $eavConfig;

    /**
     * @var \SchoolZone\Registration\Model\similarproductsattributesFactory
     */
    protected $_similarproductsattributesFactory;

    /**
     * @var \Retailinsights\Registration\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \SchoolZone\Registration\Model\similarproductsattributesFactory $similarproductsattributesFactory
     * @param \SchoolZone\Registration\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \SchoolZone\Registration\Model\SimilarproductsattributesFactory $SimilarproductsattributesFactory,
        \SchoolZone\Registration\Model\Status $status,
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


		

						// $this->addColumn(
						// 	'att_name',
						// 	[
						// 		'header' => __('Attributes_Name'),
						// 		'index' => 'att_name',
						// 		'type' => 'options',
						// 		'options' => \Retailinsights\Registration\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
						// 	]
						// );

                        $this->addColumn(
                            'school_name_text',
                            [
                                'header' => __('school Name'),
                                'index' => 'school_name_text',
                                'type' => 'text'
                                // 'options' => array('0' => __('Type 1'),'1' => __('Type 2'))
                                // 'options' => \SchoolZone\Registration\Block\Adminhtml\Similarproductsattributes\Edit\Tab::getOptionArray2()
                            ]
                        );
						$this->addColumn(
                            'class',
                            [
                                'header' => __('Class'),
                                'index' => 'class',
                                'type' => 'options',
                                // 'options' => $this->getOptionArrayName()
                                // 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \SchoolZone\Registration\Block\Adminhtml\Similarproductsattributes\Edit\Tab\Main::getOptionArray()

                                'options' => $this->getOptionArrayClass()
                            ]
                        );
                        $this->addColumn(
                            'username',
                            [
                                'header' => __('Username'),
                                'index' => 'username',
                                'type' => 'text'
                                 // 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \Retailinsights\Registration\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
                            ]
                        );
                        $this->addColumn(
                            'password',
                            [
                                'header' => __('Password'),
                                'index' => 'password',
                                'type' => 'text'
                                 // 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \Retailinsights\Registration\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
                            ]
                        );

                        $this->addColumn(
                            'admission_id',
                            [
                                'header' => __('Admission Number'),
                                'index' => 'admission_id',
                                'type' => 'text'
                                 // 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \Retailinsights\Registration\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
                            ]
                        );
		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		

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
                'url' => $this->getUrl('registration/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('registration/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
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
        return $this->getUrl('registration/*/index', ['_current' => true]);
    }

    /**
     * @param \Retailinsights\Registration\Model\similarproductsattributes|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'registration/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		public function getOptionArrayClass()
		{
            $attributeCollection = $this->eavAttribute->getCollection();

            $attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
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

            // $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
            // $options = $attribute->getSource()->getAllOptions();

            // $arr = array();
            //  foreach ($options as  $value) {
            //     echo $value['label'];
            //      $arr[]=$value['label'];
            // }


            echo "herer";

            $data_array=array(); 
            $data_array[2]='No';
            $data_array[4]='Yes';
            return($data_array);
        }
		// static public function getValueArray2()
		// {
  //           $data_array=array();
		// 	foreach(\SchoolZone\Registration\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2() as $k=>$v){
  //              $data_array[]=array('value'=>$k,'label'=>$v);
		// 	}
  //           return($data_array);

		// }
		
        static public function getOptionArrayName(){
            // $attributeCollection = $this->eavAttribute->getCollection();

            // $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
            // $options = $attribute->getSource()->getAllOptions();

            // $arr = array();
            //  foreach ($options as  $value) {
            //     echo $value['label'];
            //      $arr[]=$value['label'];
            // }
            
      

        //     print_r($arr);
        // return $arr;
        }

}