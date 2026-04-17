<?php
namespace Retailinsights\Cancellayer\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;


class UpgradeSchema implements UpgradeSchemaInterface
{
     public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;

        $installer->startSetup();
    
        if (version_compare($context->getVersion(), '2.0.2') < 0)  {
            if (!$installer->tableExists('plumrocket_rma_returns_cancel')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('plumrocket_rma_returns_cancel')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                
                ->addColumn(
                    'title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    225,
                    ['nullable' => true],
                    'title'
                )
                ->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    225,
                    ['nullable' => true],
                    'position'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    225,
                    ['nullable' => true],
                    'status'
                )
                

                    ->setComment('Promotion Store Mapping');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('plumrocket_rma_returns_cancel'),
                    $setup->getIdxName(
                        $installer->getTable('plumrocket_rma_returns_cancel'),
                        ['title','position','status'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['title','position','status'],

                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
        }
        
        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('plumrocket_rma_returns_item'),
                'reason_id_layer',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'reason_id_layer'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('plumrocket_rma_returns_item'),
                'reason_id_replace',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'reason_id_replace'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('plumrocket_rma_returns_item'),
                'reason_id_cancel',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'reason_id_cancel'
                ]
            );
        }

       
        
        $installer->endSetup();
    }
}


