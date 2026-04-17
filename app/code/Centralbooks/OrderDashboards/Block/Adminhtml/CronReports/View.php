<?php

namespace Centralbooks\OrderDashboards\Block\Adminhtml\CronReports;

class View extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getContentF()
    {
        $model = $this->_coreRegistry->registry('row_data');
        return $model->getData();
    }
}
