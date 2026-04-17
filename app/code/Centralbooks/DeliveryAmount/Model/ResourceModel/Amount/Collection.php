<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Model\ResourceModel\Amount;

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
            \Centralbooks\DeliveryAmount\Model\Amount::class,
            \Centralbooks\DeliveryAmount\Model\ResourceModel\Amount::class
        );
    }
}


