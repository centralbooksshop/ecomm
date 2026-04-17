<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Setup\Patch\Data;

use Magento\Config\Model\ConfigFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Plumrocket\RMA\Helper\Config as ConfigHelper;
use Plumrocket\RMA\Helper\Data as DataHelper;
use Plumrocket\RMA\Helper\DataFactory as DataHelperFactory;

/**
 * @since 2.4.0
 */
class UpdateConfigurations implements DataPatchInterface
{
    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var DataHelperFactory
     */
    private $dataHelperFactory;

    /**
     * @param ConfigFactory $configFactory
     * @param ConfigHelper $configHelper
     * @param DataHelperFactory $dataHelperFactory
     */
    public function __construct(
        ConfigFactory $configFactory,
        ConfigHelper $configHelper,
        DataHelperFactory $dataHelperFactory
    ) {
        $this->configFactory = $configFactory;
        $this->configHelper = $configHelper;
        $this->dataHelperFactory = $dataHelperFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        // Add store address to rma config.
        if (! $this->configHelper->getStoreAddress()
            && $address = $this->dataHelperFactory->create()->getStoreAddress()
        ) {
            $config = $this->configFactory->create();
            $config->setDataByPath(DataHelper::SECTION_ID . '/general/store_address', $address);
            $config->save();
        }
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
}
