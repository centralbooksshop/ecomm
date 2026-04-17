<?php

namespace Shipsy\EcommerceExtension\Setup;

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
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'shipsy_reference_numbers',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '255',
                    'comment' => 'test',
                    'after' => 'gift_message_id'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'shipsy_cron_error_log',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '1000',
                    'comment' => 'test',
                    'after' => 'gift_message_id'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'shipsy_tracking_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '255',
                    'comment' => 'test',
                    'after' => 'gift_message_id'
                ]
            );
        }

        $installer->endSetup();
    }
}
