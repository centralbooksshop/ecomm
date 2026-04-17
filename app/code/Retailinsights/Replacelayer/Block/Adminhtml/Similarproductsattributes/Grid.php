<?php
namespace Retailinsights\Replacelayer\Block\Adminhtml\Similarproductsattributes;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Retailinsights\Replacelayer\Model\similarproductsattributesFactory
     */
    protected $_similarproductsattributesFactory;

    /**
     * @var \Retailinsights\Replacelayer\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Retailinsights\Replacelayer\Model\similarproductsattributesFactory $similarproductsattributesFactory
     * @param \Retailinsights\Replacelayer\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Retailinsights\Replacelayer\Model\SimilarproductsattributesFactory $SimilarproductsattributesFactory,
        \Retailinsights\Replacelayer\Model\Status $status,
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
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'type' => 'text'
                // 'options' => \Retailinsights\Replacelayer\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'index' => 'position',
                'type' => 'text'
                // 'options' => \Retailinsights\Replacelayer\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
            ]
        );
		$this->addColumn(
            'status',
            [
                'header' => __('status'),
                'index' => 'status',
                'type' => 'options',
                 'options' => array('1' => 'Enabled', '0' => 'Disabled')
                // 'options' => \Retailinsights\Replacelayer\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2()
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
     * @param \Retailinsights\Replacelayer\Model\similarproductsattributes|\Magento\Framework\Object $row
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
			$data_array[0]='Disabled';
			$data_array[1]='Enabled';
            return($data_array);
		}
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Retailinsights\Replacelayer\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}