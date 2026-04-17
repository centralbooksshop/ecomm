<?php
namespace SchoolZone\Addschool\Setup;

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
		if (version_compare($context->getVersion(), '1.0.8') < 0)  {
			if (!$installer->tableExists('schools_registered')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('schools_registered')
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
						'school_type',
						Table::TYPE_TEXT,
						255,
						['nullable => false'],
						'school_type'
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
						'school_board',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'school_board'
					)
					->addColumn(
						'school_city',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'school_city'
					)
					
					// Admission No.
					->addColumn(
						'dependent_field', 
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
							'default' => ''	
						],
						'dependent_field'
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
						'description',
						Table::TYPE_TEXT,
						'2M',
						[
							'nullable' => false,
							'default' => ''	
						],
						'description'
					)
					->addColumn(
						'shipping_charge',
						Table::TYPE_TEXT,
						'2M',
						[
							'nullable' => false,
							'default' => ''	
						],
						'shipping_charge'
					)
					->addColumn(
						'enable_payu',
						Table::TYPE_BOOLEAN,
						null,
						[
							'nullable' => false,
							'default' => '1'	
						],
						'enable_payu'
					)
					->addColumn(
						'enable_cashfree',
						Table::TYPE_BOOLEAN,
						null,
						[
							'nullable' => false,
							'default' => '1'	
						],
						'enable_cashfree'
					)
					->addColumn(
						'enable_ccavenue',
						Table::TYPE_BOOLEAN,
						null,
						[
							'nullable' => false,
							'default' => '1'	
						],
						'enable_ccavenue'
					)


					
					->setComment('School Table');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('schools_registered'),
					$setup->getIdxName(
						$installer->getTable('schools_registered'),
						['school_type','school_name','school_board','school_city','dependent_field','username','password','description'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['school_type','school_name','school_board','school_city','dependent_field','username','password','description'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
		}
		if (version_compare($context->getVersion(), '1.0.9') < 0) {
			$installer->getConnection()->addColumn(
				  $installer->getTable('schools_registered'),
				  'enable_prebooking',
				  [
					  'type' => Table::TYPE_BOOLEAN,
					  'nullable' => false,
					  'default' => '0',
					  'comment' => 'Enable Prebooking'
				  ]
			  );
			  $installer->getConnection()->addColumn(
				$installer->getTable('schools_registered'),
				'prebooking_description',
				[
					'type' => Table::TYPE_TEXT,
					'length' => '2M',
					'nullable' => true,
					'default' => '',
					'comment' => 'Prebooking description'
				]
			);
			  $installer->getConnection()->addColumn(
				$installer->getTable('schools_registered'),
				'enable_preview',
				[
					'type' => Table::TYPE_BOOLEAN,
					'nullable' => false,
					'default' => '0',
					'comment' => 'Enable preview'
				]
			);
			$installer->getConnection()->addColumn(
				$installer->getTable('schools_registered'),
				'preview_description',
				[
					'type' => Table::TYPE_TEXT,
					'length' => '2M',
					'nullable' => true,
					'default' => '',
					'comment' => 'Preview description'
				]
			);
		  }
		  if (version_compare($context->getVersion(), '1.0.10') < 0) {
			$installer->getConnection()->addColumn(
				  $installer->getTable('schools_registered'),
				  'enable_cod',
				  [
					  'type' => Table::TYPE_BOOLEAN,
					  'nullable' => false,
					  'default' => '0',
					  'comment' => 'Enable COD'
				  ]
			  );
		  }
		  if (version_compare($context->getVersion(), '1.0.11') < 0) {
			$installer->getConnection()->addColumn(
				  $installer->getTable('schools_registered'),
				  'school_code',
				  [
					'type' => Table::TYPE_TEXT,
					'length' => '2M',
					'nullable' => true,
					'default' => '',
					'comment' => 'Prebooking description'
				]
			  );
		  }

		  if (version_compare($context->getVersion(), '1.0.12') < 0) {
			$installer->getConnection()->addColumn(
				  $installer->getTable('schools_registered'),
				  'school_logo',
				  [
					'type' => Table::TYPE_TEXT,
					'length' => '2M',
					'nullable' => true,
					'default' => '',
					'comment' => 'School Logo'
				]
			  );
		  }

		  if (version_compare($context->getVersion(), '1.0.13') < 0) {
			$installer->getConnection()->addColumn(
				  $installer->getTable('schools_registered'),
				  'enable_roll',
				  [
					  'type' => Table::TYPE_BOOLEAN,
					  'nullable' => false,
					  'default' => '0',
					  'comment' => 'Enable Rollnumber validation'
				  ]
			  );
		  }
		  if (version_compare($context->getVersion(), '1.0.16') < 0) {
			$installer->getConnection()->addColumn(
				  $installer->getTable('schools_registered'),
				  'location_code',
				  [
					'type' => Table::TYPE_TEXT,
					'length' => '2M',
					'nullable' => true,
					'default' => '',
					'comment' => 'School Code'
				  ]
			  );
		  }
		$installer->endSetup();
	}
}
