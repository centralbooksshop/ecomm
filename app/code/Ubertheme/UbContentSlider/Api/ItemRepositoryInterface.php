<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * UBCS slide item CRUD interface.
 * @api
 */
interface ItemRepositoryInterface
{
    /**
     * Save slide item.
     *
     * @param \Ubertheme\UbContentSlider\Api\Data\ItemInterface $item
     * @return \Ubertheme\UbContentSlider\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Ubertheme\UbContentSlider\Api\Data\ItemInterface $item);

    /**
     * Retrieve slide item.
     *
     * @param int $itemId
     * @return \Ubertheme\UbContentSlider\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($itemId);

    /**
     * Retrieve slide items matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ubertheme\UbContentSlider\Api\Data\ItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete slide item.
     *
     * @param \Ubertheme\UbContentSlider\Api\Data\ItemInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Ubertheme\UbContentSlider\Api\Data\ItemInterface $item);

    /**
     * Delete slide item by ID.
     *
     * @param int $itemId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($itemId);
}
