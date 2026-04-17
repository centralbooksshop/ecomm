<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @deprecated since 2.3.0
 * @see \Plumrocket\RMA\Api\ReturnRepositoryInterface
 * @since 2.2.0
 */
interface ReturnListInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Plumrocket\RMA\Api\Data\ReturnSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
