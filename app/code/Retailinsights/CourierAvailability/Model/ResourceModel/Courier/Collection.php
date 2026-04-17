<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Retailinsights\CourierAvailability\Model\ResourceModel\Courier;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'courier_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Retailinsights\CourierAvailability\Model\Courier::class,
            \Retailinsights\CourierAvailability\Model\ResourceModel\Courier::class
        );
    }
}

