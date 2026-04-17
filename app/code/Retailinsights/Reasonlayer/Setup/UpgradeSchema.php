<?php
namespace Retailinsights\Reasonlayer\Setup;

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
            if (!$installer->tableExists('plumrocket_rma_returns_missings')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('plumrocket_rma_returns_missings')
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
                    $installer->getTable('plumrocket_rma_returns_missings'),
                    $setup->getIdxName(
                        $installer->getTable('plumrocket_rma_returns_missings'),
                        ['title','position','status'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['title','position','status'],

                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
        }
       
        
        $installer->endSetup();
    }
}


