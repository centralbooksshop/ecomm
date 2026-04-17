<?php
namespace Centralbooks\OrderDashboards\Model\ResourceModel;

class CronReports extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('cbo_orders_payment_sync_reports', 'id');
    }
}
