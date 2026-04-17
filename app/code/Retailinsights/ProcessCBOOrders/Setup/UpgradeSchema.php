<?php
namespace Retailinsights\ProcessCBOOrders\Setup;

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

		if (version_compare($context->getVersion(), '1.1.4') < 0) {

 		   if ($installer->tableExists('cbo_assign_shippment')) {

		                $indexName = $setup->getIdxName(
                		    $installer->getTable('cbo_assign_shippment'),
                    		    ['order_id', 'driver_id', 'tracking_title', 'tracking_number'],
                    		    AdapterInterface::INDEX_TYPE_FULLTEXT
                		);

				$indexes = $installer->getConnection()->getIndexList($installer->getTable('cbo_assign_shippment'));

                		if (isset($indexes[$indexName])) {
                    		$installer->getConnection()->dropIndex(
                        		$installer->getTable('cbo_assign_shippment'),
                        		$indexName
                    		);
                		}

                $installer->getConnection()->addColumn(
                    $installer->getTable('cbo_assign_shippment'),
                    'delivered_order_id',
                    [
                        'type' => Table::TYPE_TEXT,  
                        'length' => 255,             
                        'nullable' => false,            
                        'comment' => 'Increment ID'
                    ]
                 );

                $installer->getConnection()->addIndex(
                    $installer->getTable('cbo_assign_shippment'),
                    $setup->getIdxName(
                        $installer->getTable('cbo_assign_shippment'),
                        ['order_id', 'driver_id', 'tracking_title', 'tracking_number','delivered_order_id'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['order_id', 'driver_id', 'tracking_title', 'tracking_number','delivered_order_id'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                );

    }
}	
		if (version_compare($context->getVersion(), '1.0.8') < 0)  {
			if (!$installer->tableExists('cbo_assign_shippment')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('cbo_assign_shippment')
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
						'driver_id',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'driver_id'
					)
					->addColumn(
						'tracking_title',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'tracking_title'
					)
					->addColumn(
						'tracking_number',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'tracking_number'
					)
					->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'Created At'
                    )
					->addColumn(
                        'dispatched_on',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'Dispatched On'
                    )
                    ->setComment('CBO order processing');
                    $installer->getConnection()->createTable($table);
                    
                    $installer->getConnection()->addIndex(
                        $installer->getTable('cbo_assign_shippment'),
                        $setup->getIdxName(
                            $installer->getTable('cbo_assign_shippment'),
                            ['order_id','driver_id','tracking_title','tracking_number'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                        ),
                        ['order_id','driver_id','tracking_title','tracking_number'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    );
			}
		}

		$installer->endSetup();
	}
}

