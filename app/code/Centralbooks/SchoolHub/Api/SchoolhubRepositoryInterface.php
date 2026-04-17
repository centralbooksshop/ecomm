<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SchoolhubRepositoryInterface
{

    /**
     * Save Schoolhub
     * @param \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface $schoolhub
     * @return \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface $schoolhub
    );

    /**
     * Retrieve Schoolhub
     * @param string $schoolhubId
     * @return \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($schoolhubId);

    /**
     * Retrieve Schoolhub matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Centralbooks\SchoolHub\Api\Data\SchoolhubSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Schoolhub
     * @param \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface $schoolhub
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Centralbooks\SchoolHub\Api\Data\SchoolhubInterface $schoolhub
    );

    /**
     * Delete Schoolhub by ID
     * @param string $schoolhubId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($schoolhubId);
}

