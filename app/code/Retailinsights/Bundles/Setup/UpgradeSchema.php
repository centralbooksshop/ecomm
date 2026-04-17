<?php
namespace Retailinsights\Bundles\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();
		
		if (version_compare($context->getVersion(), '0.0.2') < 0)  {
			$orderTable = 'catalog_product_bundle_selection';
			$installer->getConnection()->addColumn(
                $installer->getTable($orderTable),
                'custom_field',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => NULL,
                    'nullable' => true,
                    'comment' => 'School After given'
                ]
			);
		}
		$installer->endSetup();
	}
}
