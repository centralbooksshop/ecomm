<?php
namespace Retailinsights\Autodrivers\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();
		
		//if(version_compare($context->getVersion(), '1.1.1') < 0) {
		if (version_compare($context->getVersion(), '1.0.1') < 0)  {
			if (!$installer->tableExists('cboshipping_autodrivers')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('cboshipping_autodrivers')
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
						'driver_name',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'driver_name'
					)
					->addColumn(
						'driver_mobile',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'driver_mobile'
					)

					->addColumn(
						'auto_number',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'auto_number'
					)
					->setComment('CBO Shipping Auto drivers');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('cboshipping_autodrivers'),
					$setup->getIdxName(
						$installer->getTable('cboshipping_autodrivers'),
						['driver_name','driver_mobile','auto_number'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['driver_name','driver_mobile','auto_number'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
		}

		$installer->endSetup();
	}
}
