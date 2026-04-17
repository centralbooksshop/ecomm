<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Setup\Patch\Data;

use Magento\Customer\Model\Group;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\System\Store as SystemStore;
use Plumrocket\RMA\Model\Config\Source\Status;
use Plumrocket\RMA\Model\ReturnruleFactory;

/**
 * @since 2.4.0
 */
class CreateReturnRules implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var ReturnruleFactory
     */
    private $returnRuleFactory;

    /**
     * @param SystemStore $systemStore
     * @param ReturnruleFactory $returnRuleFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        SystemStore $systemStore,
        ReturnruleFactory $returnRuleFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->systemStore = $systemStore;
        $this->returnRuleFactory = $returnRuleFactory;

        try {
            $state->setAreaCode('adminhtml');
        } catch (\Exception $e) { // phpcs:ignore
        }
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        foreach ($this->getReturnRules() as $returnRule) {
            $this->returnRuleFactory->create()
                ->setData($returnRule)
                ->save();
        }
    }

    /**
     * Get default return rules settings
     *
     * @return array
     */
    private function getReturnRules(): array
    {
        $websiteId = key($this->systemStore->getWebsiteOptionHash());

        return [
            [
                'title' => 'Default',
                'status' => Status::STATUS_ENABLED,
                'website_id' => $websiteId,
                'customer_group_id' => implode(',', [
                    Group::NOT_LOGGED_IN_ID, 1, 2, 3
                ]),
                'priority' => 1,
                'resolution' => json_encode([
                    1 => '60',
                    2 => '90',
                    3 => '0',
                    4 => '0',
                ])
            ],
            [
                'title' => 'No returns',
                'status' => Status::STATUS_DISABLED,
                'website_id' => $websiteId,
                'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                'priority' => 0,
                'resolution' => json_encode([
                    1 => '0',
                    2 => '0',
                    3 => '0',
                    4 => '0',
                ])
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
