<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Retailinsights\CourierAvailability\Api\Data;

interface CourierInterface
{

    const COURIER_NAME = 'courier_name';
    const COURIER_ID = 'courier_id';

    /**
     * Get courier_id
     * @return string|null
     */
    public function getCourierId();

    /**
     * Set courier_id
     * @param string $courierId
     * @return \Retailinsights\CourierAvailability\Courier\Api\Data\CourierInterface
     */
    public function setCourierId($courierId);

    /**
     * Get courier_name
     * @return string|null
     */
    public function getCourierName();

    /**
     * Set courier_name
     * @param string $courierName
     * @return \Retailinsights\CourierAvailability\Courier\Api\Data\CourierInterface
     */
    public function setCourierName($courierName);
}

