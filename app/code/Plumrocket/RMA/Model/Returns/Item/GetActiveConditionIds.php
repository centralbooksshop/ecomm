<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns\Item;

use Plumrocket\RMA\Model\ResourceModel\Condition\CollectionFactory as ConditionCollectionFactory;

/**
 * @since 2.3.0
 */
class GetActiveConditionIds
{
    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Condition\CollectionFactory
     */
    private $conditionCollectionFactory;

    /**
     * @param \Plumrocket\RMA\Model\ResourceModel\Condition\CollectionFactory $conditionCollectionFactory
     */
    public function __construct(
        ConditionCollectionFactory $conditionCollectionFactory
    ) {
        $this->conditionCollectionFactory = $conditionCollectionFactory;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function get(int $storeId = 0): array
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Condition\Collection $conditionCollection */
        $conditionCollection = $this->conditionCollectionFactory->create();

        $conditionCollection
            ->addActiveFilter()
            ->addStoreFilter($storeId);

        return $conditionCollection->getAllIds();
    }
}
