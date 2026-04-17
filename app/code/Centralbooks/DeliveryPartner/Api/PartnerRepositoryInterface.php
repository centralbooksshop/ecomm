<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PartnerRepositoryInterface
{

    /**
     * Save Partner
     * @param \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface $partner
     * @return \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface $partner
    );

    /**
     * Retrieve Partner
     * @param string $partnerId
     * @return \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($partnerId);

    /**
     * Retrieve Partner matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Centralbooks\DeliveryPartner\Api\Data\PartnerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Partner
     * @param \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface $partner
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Centralbooks\DeliveryPartner\Api\Data\PartnerInterface $partner
    );

    /**
     * Delete Partner by ID
     * @param string $partnerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($partnerId);
}

