<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * UBCS slide CRUD interface.
 * @api
 */
interface SlideRepositoryInterface
{
    /**
     * Save slide.
     *
     * @param \Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide);

    /**
     * Retrieve slide.
     *
     * @param int $slideId
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($slideId);

    /**
     * Retrieve slide by slide key.
     *
     * @param string $slideKey
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySlideKey($slideKey);

    /**
     * Retrieve slides matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete slide.
     *
     * @param \Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide);

    /**
     * Delete slide by ID.
     *
     * @param int $slideId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($slideId);
}
