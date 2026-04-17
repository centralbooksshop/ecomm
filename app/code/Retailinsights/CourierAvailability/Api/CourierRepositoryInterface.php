<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Retailinsights\CourierAvailability\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CourierRepositoryInterface
{

    /**
     * Save Courier
     * @param \Retailinsights\CourierAvailability\Api\Data\CourierInterface $courier
     * @return \Retailinsights\CourierAvailability\Api\Data\CourierInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Retailinsights\CourierAvailability\Api\Data\CourierInterface $courier
    );

    /**
     * Retrieve Courier
     * @param string $courierId
     * @return \Retailinsights\CourierAvailability\Api\Data\CourierInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($courierId);

    /**
     * Retrieve Courier matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Retailinsights\CourierAvailability\Api\Data\CourierSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Courier
     * @param \Retailinsights\CourierAvailability\Api\Data\CourierInterface $courier
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Retailinsights\CourierAvailability\Api\Data\CourierInterface $courier
    );

    /**
     * Delete Courier by ID
     * @param string $courierId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($courierId);
}

