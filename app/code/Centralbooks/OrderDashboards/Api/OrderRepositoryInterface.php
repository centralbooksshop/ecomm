<?php
declare(strict_types=1);

namespace Centralbooks\OrderDashboards\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface OrderRepositoryInterface
{

    /**
     * Save Order
     * @param \Centralbooks\OrderDashboards\Api\Data\OrderInterface $order
     * @return \Centralbooks\OrderDashboards\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Centralbooks\OrderDashboards\Api\Data\OrderInterface $order
    );

    /**
     * Retrieve Order
     * @param string $orderId
     * @return \Centralbooks\OrderDashboards\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($orderId);

    /**
     * Retrieve Order matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Centralbooks\OrderDashboards\Api\Data\OrderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Order
     * @param \Centralbooks\OrderDashboards\Api\Data\OrderInterface $order
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Centralbooks\OrderDashboards\Api\Data\OrderInterface $order
    );

    /**
     * Delete Order by ID
     * @param string $orderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($orderId);
}

