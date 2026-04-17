<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order\Item as OrderItem;
use Plumrocket\RMA\Api\Data\ReturnItemInterface;
use Plumrocket\RMA\Model\Returns;

class Item extends AbstractModel implements ReturnItemInterface
{
    /**
     * @var Returns
     */
    protected $returns = null;

    /**
     * @var OrderItem
     */
    protected $orderItem = null;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Plumrocket\RMA\Model\ReasonFactory
     */
    protected $reasonFactory;

    /**
     * @var \Plumrocket\RMA\Model\ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var \Plumrocket\RMA\Model\ResolutionFactory
     */
    protected $resolutionFactory;

    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Sales\Model\Order\ItemFactory                       $orderItemFactory
     * @param \Plumrocket\RMA\Model\ReasonFactory                          $reasonFactory
     * @param \Plumrocket\RMA\Model\ConditionFactory                       $conditionFactory
     * @param \Plumrocket\RMA\Model\ResolutionFactory                      $resolutionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Plumrocket\RMA\Model\ReasonFactory $reasonFactory,
        \Plumrocket\RMA\Model\ConditionFactory $conditionFactory,
        \Plumrocket\RMA\Model\ResolutionFactory $resolutionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->orderItemFactory = $orderItemFactory;
        $this->reasonFactory = $reasonFactory;
        $this->conditionFactory = $conditionFactory;
        $this->resolutionFactory = $resolutionFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\ResourceModel\Returns\Item');
    }

    /**
     * @inheritDoc
     */
    public function getOrderItemId(): int
    {
        return (int) $this->getData(self::ORDER_ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderItemId(int $orderItemId): ReturnItemInterface
    {
        $this->setData(self::ORDER_ITEM_ID, $orderItemId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getReturnId(): int
    {
        return (int) $this->getData(self::RETURN_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReturnId(int $returnId): ReturnItemInterface
    {
        $this->setData(self::RETURN_ID, $returnId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getReasonId(): int
    {
        return (int) $this->getData(self::REASON_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReasonId(int $reasonId): ReturnItemInterface
    {
        $this->setData(self::REASON_ID, $reasonId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConditionId(): int
    {
        return (int) $this->getData(self::CONDITION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setConditionId(int $conditionId): ReturnItemInterface
    {
        $this->setData(self::CONDITION_ID, $conditionId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResolutionId(): int
    {
        return (int) $this->getData(self::RESOLUTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setResolutionId(int $resolutionId): ReturnItemInterface
    {
        $this->setData(self::RESOLUTION_ID, $resolutionId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQtyPurchased(): int
    {
        return (int) $this->getData(self::QTY_PURCHASED);
    }

    /**
     * @inheritDoc
     */
    public function setQtyPurchased(int $qtyPurchased): ReturnItemInterface
    {
        $this->setData(self::QTY_PURCHASED, $qtyPurchased);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQtyRequested(): int
    {
        return (int) $this->getData(self::QTY_REQUESTED);
    }

    /**
     * @inheritDoc
     */
    public function setQtyRequested(int $qtyRequested): ReturnItemInterface
    {
        $this->setData(self::QTY_REQUESTED, $qtyRequested);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQtyAuthorized(): ?int
    {
        $qty = $this->getData(self::QTY_AUTHORIZED);
        return $qty !== null ? (int) $qty : null;
    }

    /**
     * @inheritDoc
     */
    public function setQtyAuthorized(int $qtyAuthorized): ReturnItemInterface
    {
        $this->setData(self::QTY_AUTHORIZED, $qtyAuthorized);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQtyReceived(): ?int
    {
        $qty = $this->getData(self::QTY_RECEIVED);
        return $qty !== null ? (int) $qty : null;
    }

    /**
     * @inheritDoc
     */
    public function setQtyReceived($qtyReceived): ReturnItemInterface
    {
        $this->setData(self::QTY_RECEIVED, $qtyReceived);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQtyApproved(): ?int
    {
        $qty = $this->getData(self::QTY_APPROVED);
        return $qty !== null ? (int) $qty : null;
    }

    /**
     * @inheritDoc
     */
    public function setQtyApproved($qtyApproved): ReturnItemInterface
    {
        $this->setData(self::QTY_APPROVED, $qtyApproved);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): ReturnItemInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): ReturnItemInterface
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }

    /**
     * Get return entity
     *
     * @return Returns
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * Set return entity
     *
     * @param Returns $returns
     * @return $this
     */
    public function setReturns(Returns $returns)
    {
        $this->returns = $returns;
        $this->setReturnsId($returns->getId());
        return $this;
    }

    /**
     * Get order item
     *
     * @return OrderItem
     */
    public function getOrderItem()
    {
        if (null === $this->orderItem && ($id = $this->getOrderItemId())) {
            $this->orderItem = $this->orderItemFactory->create()->load($id);
        }

        return $this->orderItem;
    }

    /**
     * Set order item
     *
     * @param OrderItem $item
     * @return $this
     */
    public function setOrderItem(OrderItem $item)
    {
        $this->orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Get parent order item
     *
     * @return OrderItem
     */
    public function getParentOrderItem()
    {
        $orderItem = $this->getOrderItem();
        $parentOrderItem = $orderItem->getParentItem();

        if ($orderItem->getParentItemId()
            && (! $parentOrderItem || ! $parentOrderItem->getId())
        ) {
            $parentOrderItem = $this->orderItemFactory->create()
                ->load($orderItem->getParentItemId());
            $this->setParentOrderItem($parentOrderItem);
        }

        return $parentOrderItem;
    }

    /**
     * Set parent order item
     *
     * @param OrderItem $item
     * @return $this
     */
    public function setParentOrderItem(OrderItem $item)
    {
        $this->getOrderItem()->setParentItem($item);
        return $this;
    }

    /**
     * Retrieve reason label
     *
     * @return \Plumrocket\RMA\Model\Reason $reason
     */
    public function getReason()
    {
        if (null === $this->getData('reason')
            && $id = $this->getReasonId()
        ) {
            $this->setData('reason', $this->reasonFactory->create()->load($id));
        }

        return $this->getData('reason');
    }

    /**
     * Retrieve reason label
     *
     * @return string
     */
    public function getReasonLabel()
    {
        return $this->getReason() ? $this->getReason()->getLabel() : '';
    }

    /**
     * Retrieve condition
     *
     * @return \Plumrocket\RMA\Model\Condition $condition
     */
    public function getCondition()
    {
        if (null === $this->getData('condition')
            && $id = $this->getConditionId()
        ) {
            $this->setData('condition', $this->conditionFactory->create()->load($id));
        }

        return $this->getData('condition');
    }

    /**
     * Retrieve condition label
     *
     * @return string
     */
    public function getConditionLabel()
    {
        return $this->getCondition() ? $this->getCondition()->getLabel() : '';
    }

    /**
     * Retrieve resolution
     *
     * @return \Plumrocket\RMA\Model\Resolution $resolution
     */
    public function getResolution()
    {
        if (null === $this->getData('resolution')
            && $id = $this->getResolutionId()
        ) {
            $this->setData('resolution', $this->resolutionFactory->create()->load($id));
        }

        return $this->getData('resolution');
    }

    /**
     * Retrieve resolution label
     *
     * @return string
     */
    public function getResolutionLabel()
    {
        return $this->getResolution() ? $this->getResolution()->getLabel() : '';
    }
}
