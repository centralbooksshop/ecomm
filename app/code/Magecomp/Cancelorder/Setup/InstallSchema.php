<?php

namespace Magecomp\Cancelorder\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install( SchemaSetupInterface $setup, ModuleContextInterface $context )
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('magecomp_ordercancel'))
            ->addColumn(
                'ordercancel_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Entity ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_TEXT,
                50,
                [],
                'Order Id'
            )
            ->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                100,
                [],
                'Customer Email'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                50,
                [],
                'Status'
            )
            ->addColumn(
                'comment',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Comment'
            )
            ->addColumn(
                'cancelation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Cancelation Time'
            );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addIndex(
            $installer->getTable('magecomp_ordercancel'),
            $setup->getIdxName(
                $installer->getTable('magecomp_ordercancel'),
                ['order_id', 'customer_email', 'comment'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['order_id', 'customer_email', 'comment'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }
}
