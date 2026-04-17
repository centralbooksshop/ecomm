<?php
declare(strict_types=1);

namespace Centralbooks\SchoolCode\Model;

use Centralbooks\SchoolCode\Api\Data\SchoolcodeInterface;
use Centralbooks\SchoolCode\Api\Data\SchoolcodeInterfaceFactory;
use Centralbooks\SchoolCode\Api\Data\SchoolcodeSearchResultsInterfaceFactory;
use Centralbooks\SchoolCode\Api\SchoolcodeRepositoryInterface;
use Centralbooks\SchoolCode\Model\ResourceModel\Schoolcode as ResourceSchoolcode;
use Centralbooks\SchoolCode\Model\ResourceModel\Schoolcode\CollectionFactory as SchoolcodeCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SchoolcodeRepository implements SchoolcodeRepositoryInterface
{

    /**
     * @var ResourceSchoolcode
     */
    protected $resource;

    /**
     * @var SchoolcodeInterfaceFactory
     */
    protected $schoolcodeFactory;

    /**
     * @var SchoolcodeCollectionFactory
     */
    protected $schoolcodeCollectionFactory;

    /**
     * @var Schoolcode
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceSchoolcode $resource
     * @param SchoolcodeInterfaceFactory $schoolcodeFactory
     * @param SchoolcodeCollectionFactory $schoolcodeCollectionFactory
     * @param SchoolcodeSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceSchoolcode $resource,
        SchoolcodeInterfaceFactory $schoolcodeFactory,
        SchoolcodeCollectionFactory $schoolcodeCollectionFactory,
        SchoolcodeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->schoolcodeFactory = $schoolcodeFactory;
        $this->schoolcodeCollectionFactory = $schoolcodeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(SchoolcodeInterface $schoolcode)
    {
        try {
            $this->resource->save($schoolcode);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the schoolcode: %1',
                $exception->getMessage()
            ));
        }
        return $schoolcode;
    }

    /**
     * @inheritDoc
     */
    public function get($schoolcodeId)
    {
        $schoolcode = $this->schoolcodeFactory->create();
        $this->resource->load($schoolcode, $schoolcodeId);
        if (!$schoolcode->getId()) {
            throw new NoSuchEntityException(__('Schoolcode with id "%1" does not exist.', $schoolcodeId));
        }
        return $schoolcode;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->schoolcodeCollectionFactory->create();
        
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
    public function delete(SchoolcodeInterface $schoolcode)
    {
        try {
            $schoolcodeModel = $this->schoolcodeFactory->create();
            $this->resource->load($schoolcodeModel, $schoolcode->getSchoolcodeId());
            $this->resource->delete($schoolcodeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Schoolcode: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($schoolcodeId)
    {
        return $this->delete($this->get($schoolcodeId));
    }
}

