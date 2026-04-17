<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model;

use Plumrocket\RMA\Api\ReturnItemReasonRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Plumrocket\RMA\Model\ResourceModel\Reason\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Plumrocket\RMA\Api\Data\ItemReasonSearchResultInterfaceFactory;

/**
 * @since 2.3.0
 */
class ItemReasonRepository implements ReturnItemReasonRepositoryInterface
{
    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Reason\CollectionFactory
     */
    private $reasonCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Plumrocket\RMA\Api\Data\ItemReasonSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param \Plumrocket\RMA\Model\ResourceModel\Reason\CollectionFactory $reasonCollectionFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Plumrocket\RMA\Api\Data\ItemReasonSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CollectionFactory $reasonCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ItemReasonSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->reasonCollectionFactory = $reasonCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Reason\Collection $collection */
        $collection = $this->reasonCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Plumrocket\RMA\Api\Data\ItemReasonSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
