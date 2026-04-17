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

use Webkul\DeliveryBoy\Api\Data\CommentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface CommentRepositoryInterface
{
    /**
     * Save Comment
     *
     * @param CommentInterface $comment
     * @return CommentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(CommentInterface $comment);

    /**
     * Get By Id.
     *
     * @param int $commentId
     * @return CommentInterface
     */
    public function getById($commentId);

    /**
     * Get Comment List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Comment\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Comment
     *
     * @param CommentInterface $comment
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(CommentInterface $comment);

    /**
     * Delete comment by id.
     *
     * @param int $commentId
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($commentId);
}
