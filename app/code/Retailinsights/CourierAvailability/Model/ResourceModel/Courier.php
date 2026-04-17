<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Retailinsights\CourierAvailability\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Courier extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('retailinsights_courieravailability_courier', 'courier_id');
    }
}

