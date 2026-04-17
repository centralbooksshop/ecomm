<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Model;

use Centralbooks\SchoolHub\Api\Data\SchoolhubInterface;
use Centralbooks\SchoolHub\Api\Data\SchoolhubInterfaceFactory;
use Centralbooks\SchoolHub\Api\Data\SchoolhubSearchResultsInterfaceFactory;
use Centralbooks\SchoolHub\Api\SchoolhubRepositoryInterface;
use Centralbooks\SchoolHub\Model\ResourceModel\Schoolhub as ResourceSchoolhub;
use Centralbooks\SchoolHub\Model\ResourceModel\Schoolhub\CollectionFactory as SchoolhubCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SchoolhubRepository implements SchoolhubRepositoryInterface
{

    /**
     * @var ResourceSchoolhub
     */
    protected $resource;

    /**
     * @var SchoolhubInterfaceFactory
     */
    protected $schoolhubFactory;

    /**
     * @var SchoolhubCollectionFactory
     */
    protected $schoolhubCollectionFactory;

    /**
     * @var Schoolhub
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceSchoolhub $resource
     * @param SchoolhubInterfaceFactory $schoolhubFactory
     * @param SchoolhubCollectionFactory $schoolhubCollectionFactory
     * @param SchoolhubSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceSchoolhub $resource,
        SchoolhubInterfaceFactory $schoolhubFactory,
        SchoolhubCollectionFactory $schoolhubCollectionFactory,
        SchoolhubSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->schoolhubFactory = $schoolhubFactory;
        $this->schoolhubCollectionFactory = $schoolhubCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(SchoolhubInterface $schoolhub)
    {
        try {
            $this->resource->save($schoolhub);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the schoolhub: %1',
                $exception->getMessage()
            ));
        }
        return $schoolhub;
    }

    /**
     * @inheritDoc
     */
    public function get($schoolhubId)
    {
        $schoolhub = $this->schoolhubFactory->create();
        $this->resource->load($schoolhub, $schoolhubId);
        if (!$schoolhub->getId()) {
            throw new NoSuchEntityException(__('Schoolhub with id "%1" does not exist.', $schoolhubId));
        }
        return $schoolhub;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->schoolhubCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(SchoolhubInterface $schoolhub)
    {
        try {
            $schoolhubModel = $this->schoolhubFactory->create();
            $this->resource->load($schoolhubModel, $schoolhub->getSchoolhubId());
            $this->resource->delete($schoolhubModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Schoolhub: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($schoolhubId)
    {
        return $this->delete($this->get($schoolhubId));
    }
}

