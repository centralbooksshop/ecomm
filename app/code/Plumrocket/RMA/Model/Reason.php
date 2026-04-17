<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model;

use Plumrocket\RMA\Api\Data\ItemReasonInterface;

class Reason extends AbstractModel implements ItemReasonInterface
{
    /**
     * Reason payers
     */
    const PAYER_OWNER = 1;
    const PAYER_CUSTOMER = 2;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Reason::class);
    }

    /**
     * Prepare reason's payers
     * @return array
     */
    public function getAvailablePayers()
    {
        return [self::PAYER_OWNER => __('Store Owner'), self::PAYER_CUSTOMER => __('Customer')];
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return (string) $this->_getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return (int) $this->_getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getPayer(): string
    {
        return (string) $this->_getData(self::PAYER);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): int
    {
        return (int) $this->_getData(self::POSITION);
    }
}
