<?php

namespace Retailinsights\Postcode\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.1') < 0){
		   $installer->run('create table managepostcode(id int not null auto_increment, postcode varchar(100),is_shippable BOOLEAN,cod_availability BOOLEAN, primary key(id))');
		}

        $installer->endSetup();

    }
}