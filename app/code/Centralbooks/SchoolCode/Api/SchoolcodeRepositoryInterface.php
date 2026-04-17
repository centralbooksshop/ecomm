<?php
declare(strict_types=1);

namespace Centralbooks\SchoolCode\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SchoolcodeRepositoryInterface
{

    /**
     * Save Schoolcode
     * @param \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface $schoolcode
     * @return \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface $schoolcode
    );

    /**
     * Retrieve Schoolcode
     * @param string $schoolcodeId
     * @return \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($schoolcodeId);

    /**
     * Retrieve Schoolcode matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Centralbooks\SchoolCode\Api\Data\SchoolcodeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Schoolcode
     * @param \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface $schoolcode
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface $schoolcode
    );

    /**
     * Delete Schoolcode by ID
     * @param string $schoolcodeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($schoolcodeId);
}

