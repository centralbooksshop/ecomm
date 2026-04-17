<?php
namespace Retailinsights\Autodrivers\Block\Adminhtml\Listautodrivers;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Retailinsights\Postcode\Model\similarproductsattributesFactory
     */
    protected $_listautodriversFactory;

    /**
     * @var \Retailinsights\Postcode\Model\Status
     */
    // protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Retailinsights\Autodrivers\Model\ListautodriversFactory $listautodriversFactory
     * @param \Retailinsights\Autodrivers\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Retailinsights\Autodrivers\Model\ListautodriversFactory $listautodriversFactory,
        // \Retailinsights\Autodrivers\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_listautodriversFactory = $listautodriversFactory;
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
        $collection = $this->_listautodriversFactory->create()->getCollection();
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
                'driver_name',
                [
                    'header' => __('Driver name'),
                    'index' => 'driver_name',
                    'type' => 'text'
                    
                ]
            );
            $this->addColumn(
                'driver_mobile',
                [
                    'header' => __('Driver Mobile'),
                    'index' => 'driver_mobile',
                    'type' => 'text'
                    
                ]
            );
            $this->addColumn(
                'auto_number',
                [
                    'header' => __('Auto Number'),
                    'index' => 'auto_number',
                    'type' => 'text'
                    
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
        $this->getMassactionBlock()->setFormFieldName('Listautodrivers');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('autodrivers/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('autodrivers/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status')
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
        return $this->getUrl('autodrivers/*/index', ['_current' => true]);
    }

    /**
     * @param \Retailinsights\Postcode\Model\similarproductsattributes|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'autodrivers/*/edit',
            ['id' => $row->getId()]
        );
		
    }

}