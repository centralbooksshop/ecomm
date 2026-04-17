<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface {

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'ubcontentslider_slide'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ubcontentslider_slide')
        )->addColumn(
            'slide_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Slide ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Slide Title'
        )->addColumn(
            'identifier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true, 'default' => null],
            'Slide String Identifier'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Slide Description'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Slide Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Slide Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Slide Active'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Slide Sort Order'
        )->addIndex(
            $installer->getIdxName('ubcontentslider_slide', ['identifier']),
            ['identifier']
        )->setComment(
            'UbContentSlider Slide Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ubcontentslider_slide_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ubcontentslider_slide_store')
        )->addColumn(
            'slide_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Slide ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('ubcontentslider_slide_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('ubcontentslider_slide_store', 'slide_id', $installer->getTable('ubcontentslider_slide'), 'slide_id'),
            'slide_id',
            $installer->getTable('ubcontentslider_slide'),
            'slide_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ubcontentslider_slide_store', 'store_id', $installer->getTable('store'), 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'UbContentSlider Slide To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ubcontentslider_slide_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ubcontentslider_slide_item')
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Slide Item ID'
        )->addColumn(
            'slide_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Slide ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Slide Item Title'
        )->addColumn(
            'link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            ['nullable' => false, 'default' => null],
            'Slide Item Link'
        )->addColumn(
            'target',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '_self'],
            'Slide Item String Target Value (_blank,_self,_parent,_top)'
        )->addColumn(
            'content_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false, 'default' => 'image'],
            'Slide Item String Content Type'
        )->addColumn(
            'video_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true],
            'Slide Item String Video ID'
        )->addColumn(
            'image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true],
            'Slide Item String Image Name'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '1M',
            [],
            'Slide Item Description'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Slide Item Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Slide Item Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Slide Item Active'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Slide Item Sort Order'
        )->addIndex(
            $installer->getIdxName('ubcontentslider_slide_item', ['slide_id']),
            ['slide_id']
        )->addIndex(
            $installer->getIdxName('ubcontentslider_slide_item', ['video_id']),
            ['video_id']
        )->addForeignKey(
            $installer->getFkName('ubcontentslider_slide_item', 'slide_id', 'ubcontentslider_slide', 'slide_id'),
            'slide_id',
            $installer->getTable('ubcontentslider_slide'),
            'slide_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'UbContentSlider Slide Item Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addIndex(
            $installer->getTable('ubcontentslider_slide'),
            $setup->getIdxName(
                $installer->getTable('ubcontentslider_slide'),
                ['title', 'identifier', 'description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['title', 'identifier', 'description'],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
        $installer->getConnection()->addIndex(
            $installer->getTable('ubcontentslider_slide_item'),
            $setup->getIdxName(
                $installer->getTable('ubcontentslider_slide_item'),
                ['title', 'link', 'description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['title', 'link', 'description'],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );

        $installer->endSetup();
    }
}
