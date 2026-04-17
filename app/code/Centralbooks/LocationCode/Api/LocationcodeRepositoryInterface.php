<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LocationcodeRepositoryInterface
{

    /**
     * Save Locationcode
     * @param \Centralbooks\LocationCode\Api\Data\LocationcodeInterface $locationcode
     * @return \Centralbooks\LocationCode\Api\Data\LocationcodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Centralbooks\LocationCode\Api\Data\LocationcodeInterface $locationcode
    );

    /**
     * Retrieve Locationcode
     * @param string $locationcodeId
     * @return \Centralbooks\LocationCode\Api\Data\LocationcodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($locationcodeId);

    /**
     * Retrieve Locationcode matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Centralbooks\LocationCode\Api\Data\LocationcodeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Locationcode
     * @param \Centralbooks\LocationCode\Api\Data\LocationcodeInterface $locationcode
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Centralbooks\LocationCode\Api\Data\LocationcodeInterface $locationcode
    );

    /**
     * Delete Locationcode by ID
     * @param string $locationcodeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($locationcodeId);
}

