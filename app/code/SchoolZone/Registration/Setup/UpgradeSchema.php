<?php
namespace SchoolZone\Registration\Setup;

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
		if (version_compare($context->getVersion(), '1.0.3') < 0)  {
			if (!$installer->tableExists('schools_registered_by_user')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('schools_registered_by_user')
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
						'school_name',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'school_name'
					)
					->addColumn(
						'school_name_text',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'school_name_text'
					)
					->addColumn(
						'class',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'class'
					)
	
					->addColumn(
						'student_name',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'student_name'
					)
			
					->addColumn(
						'username',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'username'
					)
					->addColumn(
						'password',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'password'
					)
					->addColumn(
						'admission_id',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'admission_id'
					)
					
					->setComment('School Table');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('schools_registered_by_user'),
					$setup->getIdxName(
						$installer->getTable('schools_registered_by_user'),
						['school_type','school_name','school_name_text','class','student_name','username','password','admission_id'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['school_type','school_name','school_name_text','class','student_name','username','password','admission_id'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
		}

		$installer->endSetup();
	}
}
