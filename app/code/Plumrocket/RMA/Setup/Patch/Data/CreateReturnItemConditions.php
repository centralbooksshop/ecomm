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
use Plumrocket\RMA\Model\ConditionFactory;
use Plumrocket\RMA\Model\Config\Source\Status;

/**
 * @since 2.4.0
 */
class CreateReturnItemConditions implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    /**
     * @param ConditionFactory $conditionFactory
     */
    public function __construct(
        ConditionFactory $conditionFactory
    ) {
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        foreach ($this->getItemConditions() as $condition) {
            $this->conditionFactory->create()
                ->setData($condition)
                ->save();
        }
    }

    /**
     * Get default return item conditions settings
     *
     * @return array
     */
    private function getItemConditions(): array
    {
        return [
            [
                'title' => 'Unopened',
                'store_id' => 0,
                'position' => 1,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Opened',
                'store_id' => 0,
                'position' => 2,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Damaged',
                'store_id' => 0,
                'position' => 3,
                'status' => Status::STATUS_ENABLED,
            ],
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
