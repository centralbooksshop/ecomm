<?php
namespace Retailinsights\Backorders\Setup;

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
		
		if (version_compare($context->getVersion(), '1.0.1') < 0)  {
			if (!$installer->tableExists('backorder_items')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('backorder_items')
				)
					->addColumn(
						'id',
						Table::TYPE_INTEGER,
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
						'order_id',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'order_id'
					) 
					->addColumn(
						'back_order_id',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'back_order_id'
					)
                    ->addColumn(
						'item_id',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'item_id'
					)
					->addColumn(
						'sku',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'sku'
					)
					->addColumn(
						'qty_ordered',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'qty_ordered'
					)
					->addColumn(
						'status',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'status'
					)
					->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'Created At'
                    )
                    ->setComment('Back Orders Items List');
                    $installer->getConnection()->createTable($table);
                    
                    $installer->getConnection()->addIndex(
                        $installer->getTable('backorder_items'),
                        $setup->getIdxName(
                            $installer->getTable('cbo_assign_shippment'),
                            ['order_id','back_order_id','item_id','sku','qty_ordered','status'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                        ),
                        ['order_id','back_order_id','item_id','sku','qty_ordered','status'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    );
			}
		}

		$installer->endSetup();
	}
}
