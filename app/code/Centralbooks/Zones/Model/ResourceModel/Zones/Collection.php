<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Model\ResourceModel\Zones;

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
            \Centralbooks\Zones\Model\Zones::class,
            \Centralbooks\Zones\Model\ResourceModel\Zones::class
        );
    }
}

