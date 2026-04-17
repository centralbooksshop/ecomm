<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Model\ResourceModel\Partner;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'partner_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\DeliveryPartner\Model\Partner::class,
            \Centralbooks\DeliveryPartner\Model\ResourceModel\Partner::class
        );
    }
}

