<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns\Item;

use Plumrocket\RMA\Model\ResourceModel\Reason\CollectionFactory as ReasonCollectionFactory;

/**
 * @since 2.3.0
 */
class GetActiveReasonIds
{
    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Reason\CollectionFactory
     */
    private $reasonCollectionFactory;

    /**
     * @param \Plumrocket\RMA\Model\ResourceModel\Reason\CollectionFactory $reasonCollectionFactory
     */
    public function __construct(
        ReasonCollectionFactory $reasonCollectionFactory
    ) {
        $this->reasonCollectionFactory = $reasonCollectionFactory;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function get(int $storeId = 0): array
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Reason\Collection $reasonCollection */
        $reasonCollection = $this->reasonCollectionFactory->create();

        $reasonCollection
            ->addActiveFilter()
            ->addStoreFilter($storeId);

        return $reasonCollection->getAllIds();
    }
}
