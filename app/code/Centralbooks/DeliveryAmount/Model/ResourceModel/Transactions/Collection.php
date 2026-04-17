<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Model\ResourceModel\Transactions;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\DeliveryAmount\Model\Transactions::class,
            \Centralbooks\DeliveryAmount\Model\ResourceModel\Transactions::class
        );
    }
}



