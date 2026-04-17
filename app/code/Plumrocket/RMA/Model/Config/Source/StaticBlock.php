<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Config\Source;

use Magento\Cms\Model\BlockFactory;
use Plumrocket\RMA\Helper\Data;
use Magento\Cms\Api\Data\BlockInterface;

class StaticBlock extends AbstractSource
{
    /**
     * Block factory
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @param Data         $dataHelper
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        Data $dataHelper,
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
        parent::__construct($dataHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        $blocks = $this->blockFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(BlockInterface::IS_ACTIVE, true);

        $options = [];
        foreach ($blocks as $block) {
            $options[$block->getIdentifier()] = $block->getTitle();
        }

        return $options;
    }
}
