<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Model;

use Centralbooks\LocationCode\Api\Data\LocationcodeInterface;
use Centralbooks\LocationCode\Api\Data\LocationcodeInterfaceFactory;
use Centralbooks\LocationCode\Api\Data\LocationcodeSearchResultsInterfaceFactory;
use Centralbooks\LocationCode\Api\LocationcodeRepositoryInterface;
use Centralbooks\LocationCode\Model\ResourceModel\Locationcode as ResourceLocationcode;
use Centralbooks\LocationCode\Model\ResourceModel\Locationcode\CollectionFactory as LocationcodeCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LocationcodeRepository implements LocationcodeRepositoryInterface
{

    /**
     * @var ResourceLocationcode
     */
    protected $resource;

    /**
     * @var LocationcodeInterfaceFactory
     */
    protected $locationcodeFactory;

    /**
     * @var LocationcodeCollectionFactory
     */
    protected $locationcodeCollectionFactory;

    /**
     * @var Locationcode
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceLocationcode $resource
     * @param LocationcodeInterfaceFactory $locationcodeFactory
     * @param LocationcodeCollectionFactory $locationcodeCollectionFactory
     * @param LocationcodeSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceLocationcode $resource,
        LocationcodeInterfaceFactory $locationcodeFactory,
        LocationcodeCollectionFactory $locationcodeCollectionFactory,
        LocationcodeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->locationcodeFactory = $locationcodeFactory;
        $this->locationcodeCollectionFactory = $locationcodeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(LocationcodeInterface $locationcode)
    {
        try {
            $this->resource->save($locationcode);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the locationcode: %1',
                $exception->getMessage()
            ));
        }
        return $locationcode;
    }

    /**
     * @inheritDoc
     */
    public function get($locationcodeId)
    {
        $locationcode = $this->locationcodeFactory->create();
        $this->resource->load($locationcode, $locationcodeId);
        if (!$locationcode->getId()) {
            throw new NoSuchEntityException(__('Locationcode with id "%1" does not exist.', $locationcodeId));
        }
        return $locationcode;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->locationcodeCollectionFactory->create();
        
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
    public function delete(LocationcodeInterface $locationcode)
    {
        try {
            $locationcodeModel = $this->locationcodeFactory->create();
            $this->resource->load($locationcodeModel, $locationcode->getLocationcodeId());
            $this->resource->delete($locationcodeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Locationcode: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($locationcodeId)
    {
        return $this->delete($this->get($locationcodeId));
    }
}

