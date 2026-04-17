<?php
declare(strict_types=1);

namespace Centralbooks\OrderDashboards\Model;

use Centralbooks\OrderDashboards\Api\Data\OrderInterface;
use Centralbooks\OrderDashboards\Api\Data\OrderInterfaceFactory;
use Centralbooks\OrderDashboards\Api\Data\OrderSearchResultsInterfaceFactory;
use Centralbooks\OrderDashboards\Api\OrderRepositoryInterface;
use Centralbooks\OrderDashboards\Model\ResourceModel\Order as ResourceOrder;
use Centralbooks\OrderDashboards\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderRepository implements OrderRepositoryInterface
{

    /**
     * @var ResourceOrder
     */
    protected $resource;

    /**
     * @var OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Order
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceOrder $resource
     * @param OrderInterfaceFactory $orderFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceOrder $resource,
        OrderInterfaceFactory $orderFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(OrderInterface $order)
    {
        try {
            $this->resource->save($order);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the order: %1',
                $exception->getMessage()
            ));
        }
        return $order;
    }

    /**
     * @inheritDoc
     */
    public function get($orderId)
    {
        $order = $this->orderFactory->create();
        $this->resource->load($order, $orderId);
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order with id "%1" does not exist.', $orderId));
        }
        return $order;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->orderCollectionFactory->create();
        
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
    public function delete(OrderInterface $order)
    {
        try {
            $orderModel = $this->orderFactory->create();
            $this->resource->load($orderModel, $order->getOrderId());
            $this->resource->delete($orderModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Order: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($orderId)
    {
        return $this->delete($this->get($orderId));
    }
}

