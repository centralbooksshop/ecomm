<?php
namespace Retailinsights\Orders\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();

		if (version_compare($context->getVersion(), '1.0.11') < 0)  {
                        $orderTable = 'sales_order';
                        $installer->getConnection()->addColumn(
                $installer->getTable($orderTable),
                'shipment_location',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => NULL,
                    'nullable' => true,
                    'comment' => 'Shipment Location'
                ]
                        );
		}

		//if(version_compare($context->getVersion(), '1.1.1') < 0) {
		if (version_compare($context->getVersion(), '1.0.2') < 0)  {
			if (!$installer->tableExists('invoice_download_count')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('invoice_download_count')
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
						'invoice_id',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'invoice_id'
					)
					->addColumn(
						'invoice_count',
						Table::TYPE_TEXT,
						255,
						[
							'nullable' => false,
						],
						'invoice_count'
					)
					
					->setComment('invoice_count Table');
				$installer->getConnection()->createTable($table);

				$installer->getConnection()->addIndex(
					$installer->getTable('invoice_download_count'),
					$setup->getIdxName(
						$installer->getTable('invoice_download_count'),
						['order_id','invoice_id','invoice_count'],
						\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
					),
					['order_id','invoice_id','invoice_count'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				);
			}
			// for custom column on quote and sales_order table
			$quoteTable = 'quote';
        	$orderTable = 'sales_order';
			$installer->getConnection()->addColumn(
                $installer->getTable($quoteTable),
                'student_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => '',
                    'nullable' => true,
                    'comment' => 'Student name'
                ]
			);
			$installer->getConnection()->addColumn(
                $installer->getTable($quoteTable),
                'roll_no',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => '',
                    'nullable' => true,
                    'comment' => 'Student roll number'
                ]
			);
			$installer->getConnection()->addColumn(
                $installer->getTable($quoteTable),
                'school_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => '',
                    'nullable' => true,
                    'comment' => 'school name'
                ]
				);
			$installer->getConnection()->addColumn(
                $installer->getTable($quoteTable),
                'school_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => '',
                    'nullable' => true,
                    'comment' => 'school name'
                ]
				);
			$installer->getConnection()->addColumn(
                $installer->getTable($quoteTable),
                'school_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => '',
                    'nullable' => true,
                    'comment' => 'school code'
                ]
			);
        //Order table
        $installer->getConnection()->addColumn(
				$installer->getTable($orderTable),
				'student_name',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'default' => '',
					'nullable' => true,
					'comment' => 'Student name'
				]
				);
				$installer->getConnection()->addColumn(
				$installer->getTable($orderTable),
				'roll_no',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'default' => '',
					'nullable' => true,
					'comment' => 'Student roll number'
				]
				);
				$installer->getConnection()->addColumn(
				$installer->getTable($orderTable),
				'school_id',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'default' => '',
					'nullable' => true,
					'comment' => 'school name'
				]
				);
				$installer->getConnection()->addColumn(
				$installer->getTable($orderTable),
				'school_name',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'default' => '',
					'nullable' => true,
					'comment' => 'school name'
				]
				);
				$installer->getConnection()->addColumn(
				$installer->getTable($orderTable),
				'school_code',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'default' => '',
					'nullable' => true,
					'comment' => 'school code'
				]
				);
		}
		if (version_compare($context->getVersion(), '1.0.4') < 0)  {
			$orderTable = 'sales_order';
			$installer->getConnection()->addColumn(
                $installer->getTable($orderTable),
                'canceled_shipment',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => NULL,
                    'nullable' => true,
                    'comment' => 'Canceled shipment'
                ]
			);
		}
		if (version_compare($context->getVersion(), '1.0.9') < 0)  {
			$orderTable = 'sales_order';
			$installer->getConnection()->addColumn(
                $installer->getTable($orderTable),
                'shipment_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => NULL,
                    'nullable' => true,
                    'comment' => 'Shipment type'
                ]
			);
		}
		if (version_compare($context->getVersion(), '1.0.10') < 0)  {
			$orderTable = 'quote_item';
			$installer->getConnection()->addColumn(
                $installer->getTable($orderTable),
                'optional_items',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => NULL,
                    'nullable' => true,
                    'comment' => 'Optional Items'
                ]
			);
		}
		$installer->endSetup();
	}
}
