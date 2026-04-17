<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */

namespace Ubertheme\UbContentSlider\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        //upgrade to 1.0.7
        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            //update ubcontentslider_slide_item table
            $tableName = $setup->getTable('ubcontentslider_slide_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                //declare some new columns
                $columns = [
                    'start_time' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => true,
                        'comment' => 'Slide starting time',
                    ],
                    'end_time' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => true,
                        'comment' => 'Slide ending time',
                    ]
                ];
                //add columns
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
                //add new index
                $connection->addIndex(
                    $tableName,
                    $setup->getIdxName(
                        $tableName,
                        ['start_time', 'end_time']
                    ),
                    ['start_time', 'end_time']
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            $tableName = $setup->getTable('ubcontentslider_slide_item');
            $connection = $setup->getConnection();
            if ($connection->isTableExists($tableName) == true) {
                $query = "UPDATE {$tableName} SET `image` = REPLACE(`image`, '/ubcontentslider/images', '')";
                $connection->query($query);
            }
        }

        //upgrade to 1.1.4
        if (version_compare($context->getVersion(), '1.1.4') < 0) {
            //update ubcontentslider_slide_item table
            $tableName = $setup->getTable('ubcontentslider_slide_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $connection->addColumn($tableName, 'hot_spot', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    'nullable' => true,
                    'comment' => 'Hot Spot Data',
                ]);
                $connection->addColumn($tableName, 'additional_class', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    'nullable' => true,
                    'comment' => 'Additional class CSS',
                ]);
            }
        }

        //upgrade to 1.1.5
        if (version_compare($context->getVersion(), '1.1.5') < 0) {
            //update ubcontentslider_slide_item table
            $tableName = $setup->getTable('ubcontentslider_slide_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $connection->addColumn($tableName, 'mobile_image', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                    'comment' => 'The Image for Mobile context',
                ]);
                $connection->addColumn($tableName, 'video_cover', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                    'comment' => 'The Cover Image for Video',
                ]);
            }
        }

        $setup->endSetup();
    }
}
