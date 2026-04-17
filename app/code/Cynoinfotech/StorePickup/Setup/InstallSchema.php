<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        
        $installer->startSetup();
        
        /* create ci_stores stores table */
        
        $table = $installer->getConnection()
                ->newTable($installer->getTable('ci_stores'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary'=>true ],
                    'Entity Id'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    ['unsigned'=>true, 'nullable' => false],
                    'Store Name'
                )
                ->addColumn(
                    'store_address',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    225,
                    ['nullable' => false],
                    'Store Address'
                )
                ->addColumn(
                    'store_country',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '225',
                    ['nullable' => false],
                    'Store Country'
                )
                ->addColumn(
                    'store_state',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '225',
                    ['nullable' => false],
                    'store state'
                )
                ->addColumn(
                    'store_city',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '225',
                    ['nullable' => false],
                    'Store City'
                )
                ->addColumn(
                    'store_pincode',
                     \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '225',
                    ['nullable' => false],
                    'Store Pincode'
                )
                ->addColumn(
                    'store_image',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '225',
                    ['nullable' => false],
                    'Store Image'
                )
                ->addColumn(
                    'store_latitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    [],
                    'Store Latitude'
                )
                ->addColumn(
                    'store_longitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    [],
                    'Store Longitude'
                )
                ->addColumn(
                    'store_email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Store Email'
                )
                ->addColumn(
                    'store_phone',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [],
                    'Store Phone'
                )
                ->addColumn(
                    'store_status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Store Status'
                )
                ->setComment('CI Stores table');
                
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
