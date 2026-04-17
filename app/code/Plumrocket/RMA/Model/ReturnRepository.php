<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\RMA\Api\Data\ReturnInterface;
use Plumrocket\RMA\Api\Data\ReturnInterfaceFactory;
use Plumrocket\RMA\Api\Data\ReturnItemInterfaceFactory;
use Plumrocket\RMA\Api\Data\ReturnSearchResultsInterfaceFactory;
use Plumrocket\RMA\Api\ReturnRepositoryInterface;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Model\ResourceModel\Order\GetCustomerInfo;

/**
 * @since 2.3.0
 */
class ReturnRepository implements ReturnRepositoryInterface
{
    /**
     * @var \Plumrocket\RMA\Api\Data\ReturnInterfaceFactory
     */
    private $returnInterface;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ReturnSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ReturnItemInterfaceFactory
     */
    private $returnItemInterface;

    /**
     * @var \Plumrocket\RMA\Helper\Returns
     */
    private $returnsHelper;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Returns
     */
    private $returnResourceModel;

    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Order\GetCustomerInfo
     */
    private $getCustomerInfo;

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnInterfaceFactory $returnInterface
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Plumrocket\RMA\Api\Data\ReturnSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Plumrocket\RMA\Api\Data\ReturnItemInterfaceFactory $returnItemInterface
     * @param \Plumrocket\RMA\Helper\Returns $returnsHelper
     * @param \Plumrocket\RMA\Model\ResourceModel\Returns $returnResourceModel
     * @param \Plumrocket\RMA\Model\ResourceModel\Order\GetCustomerInfo $getCustomerInfo
     */
    public function __construct(
        ReturnInterfaceFactory $returnInterface,
        CollectionProcessorInterface $collectionProcessor,
        ReturnSearchResultsInterfaceFactory $searchResultsFactory,
        ReturnItemInterfaceFactory $returnItemInterface,
        ReturnsHelper $returnsHelper,
        \Plumrocket\RMA\Model\ResourceModel\Returns $returnResourceModel,
        GetCustomerInfo $getCustomerInfo
    ) {
        $this->returnInterface = $returnInterface;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->returnItemInterface = $returnItemInterface;
        $this->returnsHelper = $returnsHelper;
        $this->returnResourceModel = $returnResourceModel;
        $this->getCustomerInfo = $getCustomerInfo;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Returns\Collection $collection */
        $collection = $this->returnInterface->create()->getCollection();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Plumrocket\RMA\Api\Data\ReturnSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $returnItems = $this->getReturnItems($collection->getAllIds());

        $items = [];
        $orderIds = [];

        foreach ($returnItems as $item) {
            $items[$item->getParentId()][] = $item;
        }

        foreach ($collection->getItems() as $return) {
            if ($label = $return->getShippingLabel()) {
                $return->setShippingLabel($this->returnsHelper->getFileUrl($return, $label, true));
            }

            $return->setItems($items[$return->getId()]);

            $orderIds[] = $return->getOrderId();
        }

        $customersData = $this->getCustomerInfo->executeList($orderIds);
        foreach ($collection->getItems() as $return) {
            if (array_key_exists($return->getOrderId(), $customersData)) {
                $return->addData($customersData[$return->getOrderId()]);
            }
        }

        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id, bool $forceReload = false): ReturnInterface
    {
        if (! isset($this->cache[$id]) || $forceReload) {

            /** @var \Plumrocket\RMA\Api\Data\ReturnInterface|\Plumrocket\RMA\Model\Returns $return */
            $return = $this->returnInterface->create();
            $this->returnResourceModel->load($return, $id);

            if (! $return->getId()) {
                throw new NoSuchEntityException(
                    __('The return with the "%1" ID wasn\'t found. Verify the ID and try again.', $id)
                );
            }

            $customerData = $this->getCustomerInfo->execute($return->getOrderId());
            $return->addData($customerData);

            $this->cache[$id] = $return;
        }

        return $this->cache[$id];
    }

    /**
     * @inheritDoc
     */
    public function save(ReturnInterface $return): ReturnInterface
    {
        if ($return->getIdentifier() && isset($this->cache[$return->getIdentifier()])) {
            unset($this->cache[$return->getIdentifier()]);
        }

        $this->returnResourceModel->save($return->save());
        return $this->getById((int) $return->getId());
    }

    /**
     * @param array $returnIds
     * @return ResourceModel\Returns\Item\Collection
     */
    private function getReturnItems(array $returnIds)
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Returns\Item\Collection $itemCollection */
        $itemCollection = $this->returnItemInterface->create()->getCollection();
        $itemCollection->addFieldToFilter('parent_id', ['in' => $returnIds]);
        return $itemCollection;
    }
}
