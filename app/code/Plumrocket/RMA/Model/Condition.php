<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model;

use Plumrocket\RMA\Api\Data\ItemConditionInterface;

class Condition extends AbstractModel implements ItemConditionInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Condition::class);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return (string) $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): int
    {
        return (int) $this->getData(self::POSITION);
    }
}
