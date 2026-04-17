<?php

namespace Centralbooks\ClickpostExtension\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

		if (version_compare($context->getVersion(), "2.0.0", "<")) {
           $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'clickpost_courier_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '255',
                    'comment' => 'clickpost_courier_name',
                    'after' => 'gift_message_id'
                ]
            );
           
        }

       $installer->endSetup();
    }
}
