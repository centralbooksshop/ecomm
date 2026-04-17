<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Plumrocket\RMA\Api\Data\ReturnInterface;

/**
 * @since 2.3.0
 */
interface ReturnRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Plumrocket\RMA\Api\Data\ReturnSearchResultsInterface
     * @throws \Exception
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int  $id
     * @param bool $forceReload
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id, bool $forceReload = false): ReturnInterface;

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnInterface $return
     * @return \Plumrocket\RMA\Api\Data\ReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function save(ReturnInterface $return): ReturnInterface;
}
