<?php
namespace Retailinsights\WalkinCustomers\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();
		
		if (version_compare($context->getVersion(), '1.0.1') < 0)  {
			if (!$installer->tableExists('walkin_other_couriers')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('walkin_other_couriers')
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
						'tracking_title',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'tracking_title'
					)

					->addColumn(
						'tracking_number',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'tracking_number'
					)
					->setComment('walkin customer other couriers');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('walkin_other_couriers'),
					$setup->getIdxName(
						$installer->getTable('walkin_other_couriers'),
						['order_id','tracking_title','tracking_number'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['order_id','tracking_title','tracking_number'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
		}

		$installer->endSetup();
	}
}
