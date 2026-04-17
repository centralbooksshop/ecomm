<?php
namespace Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Retailinsights\Postcode\Model\similarproductsattributesFactory
     */
    protected $_similarproductsattributesFactory;

    /**
     * @var \Retailinsights\Postcode\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Retailinsights\Postcode\Model\similarproductsattributesFactory $similarproductsattributesFactory
     * @param \Retailinsights\Postcode\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Retailinsights\Postcode\Model\SimilarproductsattributesFactory $SimilarproductsattributesFactory,
        \Retailinsights\Postcode\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
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
						// 		'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
						// 	]
						// );

                        $this->addColumn(
                            'postcode',
                            [
                                'header' => __('postcode'),
                                'index' => 'postcode',
                                'type' => 'text'
                                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
                            ]
                        );
						$this->addColumn(
                            'is_shippable',
                            [
                                'header' => __('is_shippable'),
                                'index' => 'is_shippable',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
                            ]
                        );
                        $this->addColumn(
                            'cod_availability',
                            [
                                'header' => __('cod_availability'),
                                'index' => 'cod_availability',
                                'type' => 'options',
                                 'options' => array('1' => 'Yes', '0' => 'No')
                                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
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
                'url' => $this->getUrl('similarproducts/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('similarproducts/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('similarproducts/*/index', ['_current' => true]);
    }

    /**
     * @param \Retailinsights\Postcode\Model\similarproductsattributes|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'similarproducts/*/edit',
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
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}