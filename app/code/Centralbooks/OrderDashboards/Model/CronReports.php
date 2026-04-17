<?php
namespace Centralbooks\OrderDashboards\Model;

class CronReports extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'cbo_orders_payment_sync_reports';

    protected $_cacheTag = 'cbo_orders_payment_sync_reports';

    protected $_eventPrefix = 'cbo_orders_payment_sync_reports';

    protected function _construct()
    {
        $this->_init(\Centralbooks\OrderDashboards\Model\ResourceModel\CronReports::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
