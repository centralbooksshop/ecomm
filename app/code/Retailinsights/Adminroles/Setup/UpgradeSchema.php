<?php
namespace Retailinsights\Adminroles\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('admin_user'),
                'school',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 200,
                    'nullable' => true,
                    'comment' => 'school'
                ]
            );
        }
		$installer->endSetup();
	}
}
