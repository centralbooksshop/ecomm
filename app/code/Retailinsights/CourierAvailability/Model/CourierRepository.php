<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Retailinsights\CourierAvailability\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Retailinsights\CourierAvailability\Api\CourierRepositoryInterface;
use Retailinsights\CourierAvailability\Api\Data\CourierInterface;
use Retailinsights\CourierAvailability\Api\Data\CourierInterfaceFactory;
use Retailinsights\CourierAvailability\Api\Data\CourierSearchResultsInterfaceFactory;
use Retailinsights\CourierAvailability\Model\ResourceModel\Courier as ResourceCourier;
use Retailinsights\CourierAvailability\Model\ResourceModel\Courier\CollectionFactory as CourierCollectionFactory;

class CourierRepository implements CourierRepositoryInterface
{

    /**
     * @var ResourceCourier
     */
    protected $resource;

    /**
     * @var CourierInterfaceFactory
     */
    protected $courierFactory;

    /**
     * @var CourierCollectionFactory
     */
    protected $courierCollectionFactory;

    /**
     * @var Courier
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceCourier $resource
     * @param CourierInterfaceFactory $courierFactory
     * @param CourierCollectionFactory $courierCollectionFactory
     * @param CourierSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCourier $resource,
        CourierInterfaceFactory $courierFactory,
        CourierCollectionFactory $courierCollectionFactory,
        CourierSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->courierFactory = $courierFactory;
        $this->courierCollectionFactory = $courierCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(CourierInterface $courier)
    {
        try {
            $this->resource->save($courier);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the courier: %1',
                $exception->getMessage()
            ));
        }
        return $courier;
    }

    /**
     * @inheritDoc
     */
    public function get($courierId)
    {
        $courier = $this->courierFactory->create();
        $this->resource->load($courier, $courierId);
        if (!$courier->getId()) {
            throw new NoSuchEntityException(__('Courier with id "%1" does not exist.', $courierId));
        }
        return $courier;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->courierCollectionFactory->create();
        
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
    public function delete(CourierInterface $courier)
    {
        try {
            $courierModel = $this->courierFactory->create();
            $this->resource->load($courierModel, $courier->getCourierId());
            $this->resource->delete($courierModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Courier: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($courierId)
    {
        return $this->delete($this->get($courierId));
    }
}

