<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $table = $setup->getConnection()
            ->newTable($setup->getTable('ci_stores_order'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'store_pickup',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Store Id'
            )
            ->addColumn(
                'pickup_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'pickup address'
            )
            ->addColumn(
                'calendar_inputField',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                255,
                ['nullable' => false],
                'pickup date'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order Id'
            )
            ->setComment('ci Stores Pickup order Table');
            $setup->getConnection()->createTable($table);
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0)  {
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'increment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Increment id'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'store_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Store name'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'pickup_person_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Pickup person name'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'pickup_person_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Pickup person Id'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'customer_phone',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'customer phone'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'payment_method',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'payment method'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'order_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'order status'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'given_person',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Given person'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'delivery_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'length' => NULL,
                    'nullable' => false,
                    'comment' => 'Delivery Date'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ci_stores_order'),
                'store_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'store status'
                ]
            );

        }

        $setup->endSetup();
    }
}
