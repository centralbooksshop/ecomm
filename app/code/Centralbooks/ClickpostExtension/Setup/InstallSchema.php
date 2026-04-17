<?php
namespace Centralbooks\ClickpostExtension\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('clickpost_waybill')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('clickpost_waybill')
            )
            ->addColumn(
                'awb_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Manage AWB ID'
            )
			
			->addColumn(
                'courier_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable => false'],
                'Manage Courier Name'
            )
            ->addColumn(
                'waybill',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage waybill'
            )
			
			 ->addColumn(
                'courier_partner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false'],
                'Manage courier partner id'
            )
            ->addColumn(
                'shipment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Shipment Id'
            )
            ->addColumn(
                'shipment_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Shipment To'
            )
            ->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false'],
                'Manage AWB State'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Status'
            )
            ->addColumn(
                'status_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Status Type'
            )
            ->addColumn(
                'pickup_location_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false'],
                'Manage AWB Pickup Location Id'
            )
            ->addColumn(
                'return_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable => false'],
                'Manage AWB Return Address'
            )
            ->addColumn(
                'shipment_length',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Shipment Length'
            )
            ->addColumn(
                'shipment_width',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Shipment Width'
            )
            ->addColumn(
                'shipment_height',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Shipment Height'
            )
            ->addColumn(
                'status_date_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Status Date Time'
            )
            ->addColumn(
                'orderid',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Order Id'
            )
			->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Manage AWB Order Increment Id'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Manage AWB Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Manage AWB Updated At'
            )
            ->setComment('Manage AWB Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('clickpost_waybill'),
                $setup->getIdxName(
                    $installer->getTable('clickpost_waybill'),
                    ['waybill', 'courier_name', 'shipment_to', 'return_address', 'shipment_length', 'shipment_width', 'shipment_height', 'status_date_time', 'shipping_amount', 'orderid'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['waybill', 'courier_name', 'shipment_to', 'return_address', 'shipment_length', 'shipment_width', 'shipment_height', 'status_date_time', 'shipping_amount', 'orderid'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
 
        
        $installer->endSetup();
    }
}
