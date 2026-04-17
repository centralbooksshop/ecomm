<?php

namespace Ecom\Ecomexpress\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface {
	/**
	 *
	 * {@inheritdoc} @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$installer = $setup;
		
		$installer->startSetup ();
		
		$table = $installer->getConnection ()->newTable ( $installer->getTable ( 'ecomexpress_awb' ) )
		->addColumn ( 'awb_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 
				'identity' => true,
				'unsigned' => true,
				'nullable' => false,
				'primary' => true 
		], 'awb_id' )->addColumn ( 'awb', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [ 
				'unsigned' => true,
				'nullable' => false 
		], 'awb' )->addColumn ( 'shipment_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 
				'nullable' => false,
		], 'shipment_id' )->addColumn ( 'shipment_to', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [ 
				'nullable' => false,
		], 'shipment_to' )->addColumn ( 'state', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [ 
				'nullable' => false,
		], 'state' )->addColumn ( 'orderid', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [ 
				'nullable' => false 
		], 'orderid' )->addColumn ( 'status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [ 
				'nullable' => false 
		], 'status' )->addColumn ( 'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [ 
				'nullable' => false 
		], 'created_at' )->addColumn ( 'updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [ 
				'nullable' => false 
		], 'updated_at' )->addColumn ( 'awb_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [ 
				'nullable' => false 
		], 'awb_type' );
        $installer->getConnection()->createTable($table);
        
        $table = $installer->getConnection ()->newTable ( $installer->getTable ( 'ecomexpress_pincode' ) )
        ->addColumn ( 'pincode_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [
        		'identity' => true,
        		'unsigned' => true,
        		'nullable' => false,
        		'primary' => true
        ], 'pincode_id' )->addColumn ( 'pincode', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [
        		'unsigned' => true,
        		'nullable' => false
        ], 'pincode' )->addColumn ( 'city', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [
        		'nullable' => false,
        ], 'city' )->addColumn ( 'state', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, [
        		'nullable' => false,
        ], 'state' )->addColumn ( 'state_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, [
        		'nullable' => false,
        ], 'state_code' )->addColumn ( 'city_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2, [
        		'nullable' => false
        ], 'city_code' )->addColumn ( 'dccode', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, [
        		'nullable' => false
        ], 'dccode' )->addColumn ( 'date_of_discontinuance', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, [
        		'nullable' => false
        ], 'date_of_discontinuance	' )->addColumn ( 'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
        		'nullable' => false
        ], 'created_at' )->addColumn ( 'updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
        		'nullable' => false
        ], 'updated_at' );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();

    }
}