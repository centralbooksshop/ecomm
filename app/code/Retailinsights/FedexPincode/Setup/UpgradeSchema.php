<?php
namespace Retailinsights\FedexPincode\Setup;

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
		if (version_compare($context->getVersion(), '0.0.1') < 0)  {
			if (!$installer->tableExists('fedex_pincode')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('fedex_pincode')
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
						'pincode',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
						],
						'pincode'
					)
					->addColumn(
						'serviceable',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => true,
							'default' => 'No'	
						],
						'serviceable'
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
                    ->setComment('Fedex pincodes');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('fedex_pincode'),
					$setup->getIdxName(
						$installer->getTable('fedex_pincode'),
						['pincode','serviceable'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['pincode','serviceable'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
        }
        
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
			$installer->getConnection()->addColumn(
				$installer->getTable('fedex_pincode'),
				'updated_on',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					'nullable' => false,
					'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
					'comment' => 'Updated on'
				]
            );
        }

		$installer->endSetup();
	}
}
