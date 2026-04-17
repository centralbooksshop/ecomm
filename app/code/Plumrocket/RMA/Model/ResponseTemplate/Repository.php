<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model\ResponseTemplate;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Plumrocket\RMA\Api\Data\ResponseTemplateSearchResultsInterfaceFactory;
use Plumrocket\RMA\Api\ResponseTemplateRepositoryInterface;
use Plumrocket\RMA\Model\ResourceModel\Response\CollectionFactory;

/**
 * @since 2.3.0
 */
class Repository implements ResponseTemplateRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Response\CollectionFactory
     */
    private $responseTemplateCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Plumrocket\RMA\Api\Data\ResponseTemplateSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                           $searchCriteriaBuilder
     * @param \Plumrocket\RMA\Model\ResourceModel\Response\CollectionFactory         $responseTemplateCollectionFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface     $collectionProcessor
     * @param \Plumrocket\RMA\Api\Data\ResponseTemplateSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $responseTemplateCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ResponseTemplateSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->responseTemplateCollectionFactory = $responseTemplateCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        if (null === $searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        /** @var \Plumrocket\RMA\Model\ResourceModel\Response\Collection $responseTemplateCollection */
        $responseTemplateCollection = $this->responseTemplateCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $responseTemplateCollection);

        /** @var \Plumrocket\RMA\Api\Data\ResponseTemplateSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        $searchResults->setItems($responseTemplateCollection->getItems());
        $searchResults->setTotalCount($responseTemplateCollection->getSize());
        $searchResults->setSearchCriteria($searchCriteria);

        return $searchResults;
    }
}
