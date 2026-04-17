<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @since 2.3.0
 */
interface ReturnItemResolutionRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Plumrocket\RMA\Api\Data\ItemResolutionSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
