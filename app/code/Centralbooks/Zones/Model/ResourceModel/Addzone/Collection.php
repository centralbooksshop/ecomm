<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Model\ResourceModel\Addzone;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'zones_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\Zones\Model\Addzone::class,
            \Centralbooks\Zones\Model\ResourceModel\Addzone::class
        );
    }
}

