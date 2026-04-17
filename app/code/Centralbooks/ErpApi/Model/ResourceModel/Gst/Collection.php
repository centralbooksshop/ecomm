<?php
declare(strict_types=1);

namespace Centralbooks\ErpApi\Model\ResourceModel\Gst;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'gst_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\ErpApi\Model\Gst::class,
            \Centralbooks\ErpApi\Model\ResourceModel\Gst::class
        );
    }
}