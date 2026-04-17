<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Model;

use Centralbooks\Zones\Api\Data\ZonesInterface;
use Centralbooks\Zones\Api\Data\ZonesInterfaceFactory;
use Centralbooks\Zones\Api\Data\ZonesSearchResultsInterfaceFactory;
use Centralbooks\Zones\Api\ZonesRepositoryInterface;
use Centralbooks\Zones\Model\ResourceModel\Zones as ResourceZones;
use Centralbooks\Zones\Model\ResourceModel\Zones\CollectionFactory as ZonesCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ZonesRepository implements ZonesRepositoryInterface
{

    /**
     * @var ResourceZones
     */
    protected $resource;

    /**
     * @var ZonesInterfaceFactory
     */
    protected $zonesFactory;

    /**
     * @var ZonesCollectionFactory
     */
    protected $zonesCollectionFactory;

    /**
     * @var Zones
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceZones $resource
     * @param ZonesInterfaceFactory $zonesFactory
     * @param ZonesCollectionFactory $zonesCollectionFactory
     * @param ZonesSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceZones $resource,
        ZonesInterfaceFactory $zonesFactory,
        ZonesCollectionFactory $zonesCollectionFactory,
        ZonesSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->zonesFactory = $zonesFactory;
        $this->zonesCollectionFactory = $zonesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(ZonesInterface $zones)
    {
        try {
            $this->resource->save($zones);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the zones: %1',
                $exception->getMessage()
            ));
        }
        return $zones;
    }

    /**
     * @inheritDoc
     */
    public function get($zonesId)
    {
        $zones = $this->zonesFactory->create();
        $this->resource->load($zones, $zonesId);
        if (!$zones->getId()) {
            throw new NoSuchEntityException(__('Zones with id "%1" does not exist.', $zonesId));
        }
        return $zones;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->zonesCollectionFactory->create();
        
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
    public function delete(ZonesInterface $zones)
    {
        try {
            $zonesModel = $this->zonesFactory->create();
            $this->resource->load($zonesModel, $zones->getZonesId());
            $this->resource->delete($zonesModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Zones: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($zonesId)
    {
        return $this->delete($this->get($zonesId));
    }
}

