<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns\Item;

use Plumrocket\RMA\Model\ResourceModel\Resolution\CollectionFactory as ResolutionCollectionFactory;

/**
 * @since 2.3.0
 */
class GetActiveResolutionIds
{
    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Resolution\CollectionFactory
     */
    private $resolutionCollectionFactory;

    /**
     * @param \Plumrocket\RMA\Model\ResourceModel\Resolution\CollectionFactory $resolutionCollectionFactory
     */
    public function __construct(
        ResolutionCollectionFactory $resolutionCollectionFactory
    ) {
        $this->resolutionCollectionFactory = $resolutionCollectionFactory;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function get(int $storeId = 0): array
    {
        /** @var \Plumrocket\RMA\Model\ResourceModel\Resolution\Collection $resolutionCollection */
        $resolutionCollection = $this->resolutionCollectionFactory->create();

        $resolutionCollection
            ->addActiveFilter()
            ->addStoreFilter($storeId);

        return $resolutionCollection->getAllIds();
    }
}
