<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Returnrule;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Config\Model\Config\Source\Website\OptionHash;
use Plumrocket\RMA\Block\Adminhtml\Grid\Column\ActionLink;
use Plumrocket\RMA\Helper\Returnrule as ReturnruleHelper;
use Plumrocket\RMA\Model\Config\Source\Status;
use Plumrocket\RMA\Model\Returnrule;

class Grid extends Extended
{

    /**
     * Return rule factory
     * @var \Plumrocket\RMA\Model\Returnrule
     */
    private $returnRule;

    /**
     * @var ReturnruleHelper
     */
    private $returnruleHelper;

    /**
     * Website options
     * @var \Magento\Config\Model\Config\Source\Website\OptionHash
     */
    private $websiteOptions;

    /**
     * @var Status
     */
    private $statusSource;

    /**
     * @var ActionLink
     */
    private $actionLink;

    /**
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Backend\Helper\Data                           $backendHelper
     * @param \Plumrocket\RMA\Model\Returnrule                       $returnRule
     * @param \Plumrocket\RMA\Helper\Returnrule                      $returnruleHelper
     * @param \Magento\Config\Model\Config\Source\Website\OptionHash $websiteOptions
     * @param \Plumrocket\RMA\Model\Config\Source\Status             $statusSource
     * @param \Plumrocket\RMA\Block\Adminhtml\Grid\Column\ActionLink $actionLink
     * @param array                                                  $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Returnrule $returnRule,
        ReturnruleHelper $returnruleHelper,
        OptionHash $websiteOptions,
        Status $statusSource,
        ActionLink $actionLink,
        array $data = []
    ) {
        $this->returnRule           = $returnRule;
        $this->returnruleHelper     = $returnruleHelper;
        $this->websiteOptions       = $websiteOptions;
        $this->statusSource         = $statusSource;
        $this->actionLink           = $actionLink;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('manage_rma_returnrule_grid');
        $this->setDefaultSort('priority');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = $this->returnRule
            ->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header'    => __('Id'),
                'index'     => 'id',
                'type'      => 'text',
            ]
        );

        $this->addColumn(
            'title',
            [
                'header'    => __('Rule Name'),
                'index'     => 'title',
                'type'      => 'text',
            ]
        );

        $resolutions = $this->returnruleHelper->getResolutions();
        foreach ($resolutions as $resolution) {
            $index = 'res_' . $resolution->getId();
            $this->addColumn(
                $index,
                [
                    'header'    => $resolution->getTitle() . ' ' . __('Period'),
                    'index'     => $index,
                    'filter'    => false,
                    'sortable'  => false,
                    'type'      => 'text',
                    'res_id'    => $resolution->getId(),
                    'frame_callback' => [$this, 'decorateResolution'],
                    'align'     => 'center',
                ]
            );
        }

        $this->addColumn(
            'website_id',
            [
                'header'    => __('Websites'),
                'sortable'  => false,
                'index'     => 'website_id',
                'filter_condition_callback' => [$this, 'filterWebsiteCondition'],
                'options'   => $this->websiteOptions->toOptionArray(),
                'type'      => 'options',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header'    => __('Status'),
                'index'     => 'status',
                'type'      => 'options',
                'options'   => $this->statusSource->toOptionHash(),
                'frame_callback' => [$this, 'decorateStatus']
            ]
        );

        $this->addColumn(
            'priority',
            [
                'header'    => __('Priority'),
                'index'     => 'priority',
                'type'      => 'text',
                'align'     => 'center',
            ]
        );

        $this->addColumn('action', [
            'header'    => __('Action'),
            'type'      => 'text',
            'width'     => '3%',
            'filter'    => false,
            'sortable'  => false,
            'align'     => 'center',
            'frame_callback' => $this->actionLink->getFrameCallback(),
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Decorate resolution
     *
     * @param string $value
     * @param \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateResolution($value, $row, $column, $isExport)
    {
        $resolutions = $row->getResolution();
        if (isset($resolutions[$column->getResId()])) {
            return $resolutions[$column->getResId()] ?: '-';
        }

        return '';
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        if ($row->getStatus()) {
            $cell = '<span class="grid-severity-notice"><span>' . __('Enabled') . '</span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span>' . __('Disabled') . '</span></span>';
        }
        return $cell;
    }

    /**
     * Filter by website id
     * @return $this
     */
    public function filterWebsiteCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addFieldToFilter('website_id', ['finset' => $value]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()
            ->addItem('enable', [
                'label'     => __('Enable'),
                'url'       => $this->getUrl('*/*/massStatus', ['status' => '1'])
            ])
            ->addItem('disable', [
                'label'     => __('Disable'),
                'url'       => $this->getUrl('*/*/massStatus', ['status' => '0'])
            ])
            ->addItem('delete', [
                'label'     => __('Delete'),
                'url'       => $this->getUrl('*/*/delete'),
                'confirm'   => [
                    'title'     => 'Delete items',
                    'message'   => 'Delete selected items?',
                ]
            ]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        return parent::_toHtml() . '<script>requirejs(["prgrid"]);</script>';
    }
}
