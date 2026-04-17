<?php
namespace Shipsy\EcommerceExtension\Model\Plugin\Sales\Order;
 
class Grid
{
    public function afterSearch($intercepter, $collection) {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName('sales_order_grid')) {
 
            $leftJoinTableName = $collection->getConnection()->getTableName('sales_order');
 
            $collection
                ->getSelect()
                ->joinLeft(
                    ['so'=>$leftJoinTableName],
                    "so.entity_id = main_table.entity_id",
                    [
                        'shipsy_reference_numbers' => 'so.shipsy_reference_numbers',
                        'shipsy_tracking_url' => 'so.shipsy_tracking_url',
                        'shipsy_cron_error_log' => 'so.shipsy_cron_error_log'
                    ]
                );
 
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
 
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
 
 
        }
        return $collection;
    }
}