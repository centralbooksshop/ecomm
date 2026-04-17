<?php

namespace Retailinsights\Registers\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const CUSTOM_ATTRIBUTE_ID = 'mobile_number';
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.11', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                self::CUSTOM_ATTRIBUTE_ID,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Mobile Number'
                ]
            );
        }

        $setup->endSetup();


    }
}