<?php
namespace Retailinsights\FedexPincode\Block\Adminhtml\FedexPincodeList;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Retailinsights\FedexPincode\Model\FedexPincodeListFactory
     */
    protected $_FedexPincodeListFactory;

    /**
     * @var \Retailinsights\FedexPincode\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Retailinsights\FedexPincode\Model\FedexPincodeListFactory $FedexPincodeListFactory
     * @param \Retailinsights\FedexPincode\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Retailinsights\FedexPincode\Model\FedexPincodeListFactory $FedexPincodeListFactory,
        // \Retailinsights\FedexPincode\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_FedexPincodeListFactory = $FedexPincodeListFactory;
        // $this->_status = $status;
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
        $collection = $this->_FedexPincodeListFactory->create()->getCollection();
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
            'pincode',
            [
                'header' => __('Pincode'),
                'index' => 'pincode',
                'type' => 'text'
                // 'options' => \Retailinsights\FedexPincode\Block\Adminhtml\FedexPincodeList\Grid::getOptionArray2()
            ]
        );
        $this->addColumn(
            'serviceable',
            [
                'header' => __('Serviceable'),
                'index' => 'serviceable',
                'type' => 'options',
                    'options' => array('Yes' => 'Yes', 'No' => 'No')
                // 'options' => \Retailinsights\FedexPincode\Block\Adminhtml\FedexPincodeList\Grid::getOptionArray2()
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
        //$this->getMassactionBlock()->setTemplate('RetailInsights_SimilarProducts::FedexPincodeList/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('FedexPincode');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('fedexpincode/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        // $statuses = $this->_status->getOptionArray();

        // $this->getMassactionBlock()->addItem(
        //     'status',
        //     [
        //         'label' => __('Change status'),
        //         'url' => $this->getUrl('similarproducts/*/massStatus', ['_current' => true]),
        //         'additional' => [
        //             'visibility' => [
        //                 'name' => 'status',
        //                 'type' => 'select',
        //                 'class' => 'required-entry',
        //                 'label' => __('Status'),
        //                 'values' => $statuses
        //             ]
        //         ]
        //     ]
        // );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('fedexpincode/*/index', ['_current' => true]);
    }

    /**
     * @param \Retailinsights\FedexPincode\Model\FedexPincodeList|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'fedexpincode/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray2()
		{
            $data_array=array(); 
			$data_array['No']='No';
			$data_array['Yes']='Yes';
            return($data_array);
		}
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Retailinsights\FedexPincode\Block\Adminhtml\FedexPincodeList\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}