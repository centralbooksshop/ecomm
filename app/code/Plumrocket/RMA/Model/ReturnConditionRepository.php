<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model;

use Plumrocket\RMA\Api\ReturnItemConditionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Plumrocket\RMA\Model\ResourceModel\Condition\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Plumrocket\RMA\Api\Data\ItemResolutionSearchResultInterfaceFactory;

/**
 * @since 2.3.0
 */
class ReturnConditionRepository implements ReturnItemConditionRepositoryInterface
{
    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Condition\CollectionFactory
     */
    private $conditionCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Plumrocket\RMA\Api\Data\ItemResolutionSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param \Plumrocket\RMA\Model\ResourceModel\Condition\CollectionFactory $conditionCollectionFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Plumrocket\RMA\Api\Data\ItemResolutionSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CollectionFactory $conditionCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ItemResolutionSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->conditionCollectionFactory = $conditionCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Resolution\Collection $collection */
        $collection = $this->conditionCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Plumrocket\RMA\Api\Data\ItemResolutionSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
