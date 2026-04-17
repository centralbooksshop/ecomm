<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AddzoneRepositoryInterface
{

    /**
     * Save Zones
     * @param \Centralbooks\Zones\Api\Data\AddzoneInterface $zones
     * @return \Centralbooks\Zones\Api\Data\AddzoneInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Centralbooks\Zones\Api\Data\AddzoneInterface $zones
    );

    /**
     * Retrieve Zones
     * @param string $zonesId
     * @return \Centralbooks\Zones\Api\Data\AddzoneInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($zonesId);

    /**
     * Retrieve Zones matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Centralbooks\Zones\Api\Data\ZonesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Zones
     * @param \Centralbooks\Zones\Api\Data\AddzoneInterface $zones
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Centralbooks\Zones\Api\Data\AddzoneInterface $zones
    );

    /**
     * Delete Zones by ID
     * @param string $zonesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($zonesId);
}

