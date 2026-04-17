<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Api;

use Webkul\DeliveryBoy\Api\Data\RatingInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface RatingRepositoryInterface
{
    /**
     * Save Rating.
     *
     * @param RatingInterface $rating
     * @return RatingInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(RatingInterface $rating);

    /**
     * Get RatingInterface
     *
     * @param int $ratingId
     * @return RatingInterface
     */
    public function getById($ratingId);

    /**
     * GEt Rating list.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Rating\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Rating.
     *
     * @param RatingInterface $rating
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(RatingInterface $rating);

    /**
     * Delete Rating by Id.
     *
     * @param int $ratingId
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($ratingId);
}
