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
use Plumrocket\RMA\Model\ResolutionFactory;

/**
 * @since 2.4.0
 */
class CreateReturnResolutions implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResolutionFactory
     */
    private $resolutionFactory;

    /**
     * @param ResolutionFactory $resolutionFactory
     */
    public function __construct(
        ResolutionFactory $resolutionFactory
    ) {
        $this->resolutionFactory = $resolutionFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        foreach ($this->getResolutions() as $resolution) {
            $this->resolutionFactory->create()
                ->setData($resolution)
                ->save();
        }
    }

    /**
     * Get default return resolutions settings
     *
     * @return array
     */
    private function getResolutions(): array
    {
        return [
            [
                'title' => 'Exchange',
                'store_id' => 0,
                'position' => 1,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Return',
                'store_id' => 0,
                'position' => 2,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Repair',
                'store_id' => 0,
                'position' => 3,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Store Credit',
                'store_id' => 0,
                'position' => 4,
                'status' => Status::STATUS_DISABLED,
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
