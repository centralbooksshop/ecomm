<?php
namespace SchoolZone\Search\Block\Adminhtml\NotifyReport;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $eavAttribute;
     protected $eavConfig;

    /**
     * @var \SchoolZone\Addschool\Model\NotifyReportFactory
     */
    protected $_NotifyReportFactory;

    /**
     * @var \Retailinsights\Postcode\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \SchoolZone\Search\Model\NotifyReportFactory $NotifyReportFactory
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
        \SchoolZone\Search\Model\NotifyReportFactory $NotifyReportFactory,
        // \SchoolZone\Addschool\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Attribute $eavAttribute,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->_NotifyReportFactory = $NotifyReportFactory;
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
        $collection = $this->_NotifyReportFactory->create()->getCollection();
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
                            'name',
                            [
                                'header' => __('Name'),
                                'index' => 'name',
                                'type' => 'text',
                                'width' => 10
                            ]
                        );
                        $this->addColumn(
                            'phone',
                            [
                                'header' => __('Phone'),
                                'index' => 'phone',
                                'type' => 'text'
                            ]
                        );
                        $this->addColumn(
                            'email',
                            [
                                'header' => __('Email'),
                                'index' => 'email',
                                'type' => 'text'
                            ]
                        );
                        $this->addColumn(
                            'school_name',
                            [
                                'header' => __('School Name'),
                                'index' => 'school_name',
                                'type' => 'text'
                            ]
                        );
                        $this->addColumn(
                            'school_address',
                            [
                                'header' => __('School Address'),
                                'index' => 'school_address',
                                'type' => 'text'
                            ]
                        );
                        $this->addColumn(
                            'message',
                            [
                                'header' => __('Message'),
                                'index' => 'message',
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
        //$this->getMassactionBlock()->setTemplate('RetailInsights_SimilarProducts::similarproductsattributes/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('notifyreport');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('schoolnotify/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        // $statuses = $this->_status->getOptionArray();

        // $this->getMassactionBlock()->addItem(
        //     'status',
        //     [
        //         'label' => __('Change status'),
        //         'url' => $this->getUrl('addschool/*/massStatus', ['_current' => true]),
        //         'additional' => [
        //             'visibility' => [
        //                 'name' => 'status',
        //                 'type' => 'select',
        //                 'class' => 'required-entry',
        //                 'label' => __('Status'),
        //                 'values' => ''
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
        return $this->getUrl('schoolnotify/*/index', ['_current' => true]);
    }

    /**
     * @param \Retailinsights\Postcode\Model\similarproductsattributes|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'schoolnotify/*/edit',
            ['id' => $row->getId()]
        );
		
    }	

}