<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Plumrocket\RMA\Model\Config\Source\Status;
use Plumrocket\RMA\Model\ReasonFactory;

/**
 * @since 2.4.0
 */
class CreateReturnReasons implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ReasonFactory
     */
    private $reasonFactory;

    /**
     * @param ReasonFactory $reasonFactory
     */
    public function __construct(
        ReasonFactory $reasonFactory
    ) {
        $this->reasonFactory = $reasonFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        foreach ($this->getReasons() as $reason) {
            $this->reasonFactory->create()
                ->setData($reason)
                ->save();
        }
    }

    /**
     * Get default return reasons settings
     *
     * @return array
     */
    private function getReasons(): array
    {
        return [
            [
                'title' => 'My item was damaged during shipment',
                'store_id' => 0,
                'position' => 1,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'My item was damaged (not during shipment)',
                'store_id' => 0,
                'position' => 2,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'I received the wrong item',
                'store_id' => 0,
                'position' => 3,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'The item I received is different than the description',
                'store_id' => 0,
                'position' => 4,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'I no longer need/want my item',
                'store_id' => 0,
                'position' => 5,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => "My item has a manufacturer's defect",
                'store_id' => 0,
                'position' => 6,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => "Other",
                'store_id' => 0,
                'position' => 7,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion(): string
    {
        return '2.0.0';
    }
}
