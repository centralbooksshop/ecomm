<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('fedexlabel');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'fedexlabel'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'fedexlabel_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Label ID'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Title'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Order ID'
            )->addColumn(
                'shipment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Shipment ID'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['nullable' => false, 'default' => 'shipment'],
                'Type'
            )->addColumn(
                'type_print',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                11,
                ['nullable' => false, 'default' => 'pdf'],
                'Type print'
            )->addColumn(
                'trackingnumber',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Tracking number'
            )->addColumn(
                'labelname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Label name'
            )->addColumn(
                'lstatus',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                6,
                ['nullable' => false, 'default' => '1'],
                'Status'
            )->addColumn(
                'statustext',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Status description'
            )->addColumn(
                'rva_printed',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Printed'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Store Id'
                ]
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Order Increment Id'
                ]
            )->addColumn(
                'shipment_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Shipment or Creditmemo Increment Id'
                ]
            )->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '7,2',
                [
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Price'
                ]
            )->addColumn(
                'currency',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [
                    'nullable' => false,
                    'default' => 'USD',
                    'comment' => 'Currency'
                ]
            )->addColumn(
                'type_2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                [
                    'nullable' => false,
                    'default' => 'shipment',
                    'comment' => 'Type 2'
                ]
            )->addColumn(
                'xmllog',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Xml log'
                ]
            )->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Update time'
            )->setComment(
                'Fedex labels'
            );
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('fedexlabelconformity');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'fedexlabelaccount'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'conformity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Conformity ID'
            )->addColumn(
                'method_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'method id'
            )->addColumn(
                'fedexmethod_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => ''],
                'fedex method id'
            )->addColumn(
                'country_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'country ids'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => 1],
                'store id'
            )->setComment(
                'Fedex Conformity'
            );
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('fedexlabelaccount');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'fedexlabelaccount'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Account ID'
            )->addColumn(
                'companyname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Company name'
            )->addColumn(
                'attentionname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Attention name'
            )->addColumn(
                'address1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Address 1'
            )->addColumn(
                'address2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Address 2'
            )->addColumn(
                'address3',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Address 3'
            )->addColumn(
                'country',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Country'
            )->addColumn(
                'postalcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Postal code'
            )->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'City'
            )->addColumn(
                'province',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Province'
            )->addColumn(
                'telephone',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Telephone'
            )->addColumn(
                'fax',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Fax'
            )->addColumn(
                'accountnumber',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Account number'
            )->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Update time'
            )->setComment(
                'Fedex Account'
            );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
