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
interface ResponseTemplateRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Plumrocket\RMA\Api\Data\ResponseTemplateSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);
}
