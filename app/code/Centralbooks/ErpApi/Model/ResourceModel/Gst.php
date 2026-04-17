<?php
declare(strict_types=1);

namespace Centralbooks\ErpApi\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Gst extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('erp_gst', 'gst_id');
    }
}