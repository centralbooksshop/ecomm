<?php
namespace SchoolZone\Search\Setup;

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
		if (version_compare($context->getVersion(), '1.0.2') < 0)  {
			if (!$installer->tableExists('school_notify_report')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('school_notify_report')
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
						'name',
						Table::TYPE_TEXT,
						255,
						['nullable => false'],
						'name'
					)

					->addColumn(
						'phone',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'phone'
					)
					->addColumn(
						'email',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'email'
					)
	
					->addColumn(
						'school_name',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'school_name'
					)
					->addColumn(
						'school_address',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'school_address'
					)
					->addColumn(
						'message',
						Table::TYPE_TEXT,
						'2M',
						[
							'nullable' => true,
						],
						'message'
					)
					->setComment('School Notify me');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('school_notify_report'),
					$setup->getIdxName(
						$installer->getTable('school_notify_report'),
						['name','phone','email','school_name','school_address','message'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['name','phone','email','school_name','school_address','message'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
		}


		if (version_compare($context->getVersion(), '1.0.4') < 0) {
			$installer->getConnection()->addColumn(
				$installer->getTable('school_notify_report'),
				'notify_status',
				[
					'type' => Table::TYPE_TEXT,
					'length' => 200,
					'nullable' => false,
					'default' => 'New',
					'comment' => 'Status'
				]
			);

			$installer->getConnection()->addColumn(
			  $installer->getTable('school_notify_report'),
			  'is_deleted',
			  [
				  'type' => Table::TYPE_TEXT,
				  'length' => 200,
				  'nullable' => false,
				  'default' => 'false',
				  'comment' => 'is deleted'
			  ]
		  );

			  $installer->getConnection()->addColumn(
				$installer->getTable('school_notify_report'),
				'notify_created_at',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					'nullable' => false,
					'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
					'comment' => 'Created at'
				]
				);

				$installer->getConnection()->addColumn(
					$installer->getTable('school_notify_report'),
					'notify_updates_at',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
						'nullable' => false,
						'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
						'comment' => 'Updated at'
					]
				);

		  }

		
		$installer->endSetup();
	}
}
