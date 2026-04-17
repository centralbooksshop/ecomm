<?php
namespace Retailinsights\FedExCustom\Setup;

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
		if (version_compare($context->getVersion(), '0.0.2') < 0)  {
			if (!$installer->tableExists('fedex_response')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('fedex_response')
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
						'fedex_label_created',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'fedex_label_created'
					)
                    ->addColumn(
						'additional_subjects',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'additional_subjects'
					)
                    ->addColumn(
						'trackingnumber',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'trackingnumber'
					)
                    ->addColumn(
						'binarybarcode',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'binarybarcode'
					)
                    ->addColumn(
						'stringbarcode',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'stringbarcode'
					)
                    ->addColumn(
						'formid',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'formid'
					)
                    ->addColumn(
						'ursaCode',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'ursaCode'
					)
                    ->addColumn(
						'cfamount',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'cfamount'
					)
                    ->addColumn(
						'operational_instructions',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'operational_instructions'
					)

                    ->addColumn(
						'created_at',
						\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
						255,
						[
							'nullable' => false,
							'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT	
						],
						'created at'
                    )
                    ->addColumn(
						'updated_on',
						\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
						255,
						[
							'nullable' => false,
							'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE	
						],
						'created at'
                    )
                    ->setComment('Fedex Response data');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('fedex_response'),
					$setup->getIdxName(
						$installer->getTable('fedex_response'),
						['fedex_label_created','order_id', 'additional_subjects','trackingnumber','binarybarcode','stringbarcode','formid','ursaCode','cfamount','operational_instructions'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['fedex_label_created','order_id','additional_subjects','trackingnumber','binarybarcode','stringbarcode','formid','ursaCode','cfamount','operational_instructions'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
        }
        
		$installer->endSetup();
	}
}
