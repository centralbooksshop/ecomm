<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Model;

use Ubertheme\UbContentSlider\Api\Data;
use Ubertheme\UbContentSlider\Api\SlideRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Ubertheme\UbContentSlider\Model\ResourceModel\Slide as ResourceSlide;
use Ubertheme\UbContentSlider\Model\ResourceModel\Slide\CollectionFactory as SlideCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SlideRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SlideRepository implements SlideRepositoryInterface
{
    /**
     * @var ResourceSlide
     */
    protected $resource;

    /**
     * @var SlideFactory
     */
    protected $slideFactory;

    /**
     * @var SlideCollectionFactory
     */
    protected $slideCollectionFactory;

    /**
     * @var Data\SlideSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Ubertheme\UbContentSlider\Api\Data\SlideInterfaceFactory
     */
    protected $dataSlideFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceSlide $resource
     * @param SlideFactory $slideFactory
     * @param Data\SlideInterfaceFactory $dataSlideFactory
     * @param SlideCollectionFactory $slideCollectionFactory
     * @param Data\SlideSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceSlide $resource,
        SlideFactory $slideFactory,
        Data\SlideInterfaceFactory $dataSlideFactory,
        SlideCollectionFactory $slideCollectionFactory,
        Data\SlideSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->slideFactory = $slideFactory;
        $this->slideCollectionFactory = $slideCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSlideFactory = $dataSlideFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Slide data
     *
     * @param \Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide
     * @return Slide
     * @throws CouldNotSaveException
     */
    public function save(\Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide)
    {
        try {
            $this->resource->save($slide);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $slide;
    }

    /**
     * Load Slide data by given Slide Identity
     *
     * @param string $slideId
     * @return Slide
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($slideId)
    {
        $slide = $this->slideFactory->create();
        $slide->load($slideId);
        if (!$slide->getId()) {
            throw new NoSuchEntityException(__('Slide with id "%1" does not exist.', $slideId));
        }
        return $slide;
    }

    /**
     * Load slide by given slide key
     *
     * @param string $slideKey
     * @return Data\SlideInterface|Slide
     * @throws NoSuchEntityException
     */
    public function getBySlideKey($slideKey)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->slideCollectionFactory->create()
            ->addFieldToSelect(['slide_id', 'title', 'identifier', 'is_active'])
            ->addFieldToFilter('identifier', ['eq' => $slideKey])
            ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbContentSlider\Model\Slide::STATUS_ENABLED])
            ->addStoreFilter($storeId, true)
            ->addOrder('slide_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        /** @var \Ubertheme\UbContentSlider\Model\Slide $slide */
        $slide = $collection->getFirstItem();
        if (!$slide->getId()) {
            throw new NoSuchEntityException(
                __('The Slider with the slide key "%1" doesn\'t exist or it was disabled.', $slideKey)
            );

        }

        return $slide;
    }

    /**
     * Load Slide data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Ubertheme\UbContentSlider\Model\ResourceModel\Slide\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->slideCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $slides = [];
        /** @var Slide $slideModel */
        foreach ($collection as $slideModel) {
            $slideData = $this->dataSlideFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $slideData,
                $slideModel->getData(),
                'Ubertheme\UbContentSlider\Api\Data\SlideInterface'
            );
            $slides[] = $this->dataObjectProcessor->buildOutputDataArray(
                $slideData,
                'Ubertheme\UbContentSlider\Api\Data\SlideInterface'
            );
        }
        $searchResults->setItems($slides);
        return $searchResults;
    }

    /**
     * Delete Slide
     *
     * @param \Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Ubertheme\UbContentSlider\Api\Data\SlideInterface $slide)
    {
        try {
            $this->resource->delete($slide);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Slide by given Slide Identity
     *
     * @param string $slideId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($slideId)
    {
        return $this->delete($this->getById($slideId));
    }
}
