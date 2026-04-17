<?php
namespace Retailinsights\Bundles\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddApproveSchoolPreview implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Constructor
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply the data patch
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Add "Approve School Preview" attribute
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'approve_school_preview', // attribute code
            [
                'type' => 'int',
                'label' => 'Approve School Preview',
                'input' => 'boolean', // Yes/No dropdown
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'required' => false,
                'sort_order' => 1000,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => true,
                'default' => 0,
                'group' => 'Attributes',
				'apply_to' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
                'backend' => '',
                'frontend' => '',
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Dependencies
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Aliases
     */
    public function getAliases()
    {
        return [];
    }
}
