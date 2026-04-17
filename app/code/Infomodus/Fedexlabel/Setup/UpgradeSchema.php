<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Fedexlabel\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->createAddressTable($setup);
            $this->createBoxesTable($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->addTin($setup);
        }
        if (version_compare($context->getVersion(), '1.0.8') < 0)  {
			$fedexTable = 'fedexlabel';
			$setup->getConnection()->addColumn(
                $setup->getTable($fedexTable),
                'responseData',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'default' => NULL,
                    'nullable' => true,
                    'comment' => 'Fedex response data'
                ]
			);
		}

        $setup->endSetup();
    }

    public function addTin(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('fedexlabel_address');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'tin_type',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'tin type'
                ]
            );

            $setup->getConnection()->addColumn(
                $tableName,
                'tin_number',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'tin number'
                ]
            );
        }
    }

    public function createAddressTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('fedexlabel_address');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'fedexlabel_address'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'address_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'address ID'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'name'
            )->addColumn(
                'company',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'company'
            )->addColumn(
                'attention',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'attention'
            )->addColumn(
                'phone',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'phone'
            )->addColumn(
                'street_one',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'street_one'
            )->addColumn(
                'street_two',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'street_two'
            )->addColumn(
                'room',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'room'
            )->addColumn(
                'floor',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'floor'
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'city'
            )->addColumn(
                'province_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'province_code'
            )->addColumn(
                'urbanization',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'urbanization'
            )->addColumn(
                'postal_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'postal_code'
            )->addColumn(
                'country',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'country'
            )->addColumn(
                'residential',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'residential'
            )->setComment(
                'Fedex Addresses'
            );
            $setup->getConnection()->createTable($table);
        }
    }

    public function createBoxesTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('fedexlabel_box');
        if ($setup->getConnection()->isTableExists($tableName) == false) {
            /**
             * Create table 'fedexlabel_box'
             */
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'box_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Box ID'
            )->addColumn(
                'enable',
                Table::TYPE_INTEGER,
                2,
                ['nullable' => false, 'default' => 1],
                'enable'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'name'
            )->addColumn(
                'width',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'width'
            )->addColumn(
                'outer_width',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'outer_width'
            )->addColumn(
                'height',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'height'
            )->addColumn(
                'outer_height',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'outer_height'
            )->addColumn(
                'lengths',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'lengths'
            )->addColumn(
                'outer_lengths',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'outer_lengths'
            )->addColumn(
                'max_weight',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'max_weight'
            )->addColumn(
                'empty_weight',
                Table::TYPE_DECIMAL,
                '7,2',
                ['nullable' => false, 'default' => 0],
                'empty_weight'
            )->setComment(
                'FedEx Boxes'
            );
            $setup->getConnection()->createTable($table);
        }
    }
}
