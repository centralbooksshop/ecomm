<?php
namespace SchoolZone\Search\Model\ResourceModel;

class NotifyReport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('school_notify_report', 'id');
    }
}
