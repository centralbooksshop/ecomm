<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Retailinsights\CourierAvailability\Model;

use Magento\Framework\Model\AbstractModel;
use Retailinsights\CourierAvailability\Api\Data\CourierInterface;

class Courier extends AbstractModel implements CourierInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Retailinsights\CourierAvailability\Model\ResourceModel\Courier::class);
    }

    /**
     * @inheritDoc
     */
    public function getCourierId()
    {
        return $this->getData(self::COURIER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCourierId($courierId)
    {
        return $this->setData(self::COURIER_ID, $courierId);
    }

    /**
     * @inheritDoc
     */
    public function getCourierName()
    {
        return $this->getData(self::COURIER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCourierName($courierName)
    {
        return $this->setData(self::COURIER_NAME, $courierName);
    }
}

