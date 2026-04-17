<?php
namespace Retailinsights\ProcessCBOOrders\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeData implements UpgradeDataInterface
{
    
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		if (version_compare($context->getVersion(), '1.0.7', '<')) {
			$installer = $setup;
            $installer->startSetup(); 

            $data[] = ['status' => 'dispatched_to_courier', 'label' => 'Dispatched To Courier'];
            $data[] = ['status' => 'order_delivered', 'label' => 'Order Delivered'];
            $data[] = ['status' => 'order_not_delivered', 'label' => 'Order not Delivered'];
            $setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

            $setup->getConnection()->insertArray(
                $setup->getTable('sales_order_status_state'),
                ['status', 'state', 'is_default','visible_on_front'],
                [
                    ['dispatched_to_courier','complete', '0', '1'],
                    ['order_delivered','complete', '0', '1'],
                    ['order_not_delivered','complete', '0', '1']
                ]
            );
            $setup->endSetup();
		}
	}
}