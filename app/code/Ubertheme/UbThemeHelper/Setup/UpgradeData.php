<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */

namespace Ubertheme\UbThemeHelper\Setup;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $catalogSetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->catalogSetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $attributeCode = 'ub_hover_image';
            $isExisted = $catalogSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            if( !$isExisted ) {
                $settings = [
                    'type' => 'varchar',
                    'label' => 'UB Hover Image',
                    'input' => 'media_image',
                    'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'used_in_product_listing' => true,
                    'required' => false,
                    'sort_order' => 10
                ];
                $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $settings);
            }
        }

        $setup->endSetup();
    }
}
