<?php
namespace Retailinsights\Orders\Model\Plugin\Sales\Order;
 
 
class Grid
{
 
    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'cbo_assign_shippment';
 
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
 
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
 
            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'=>$leftJoinTableName],
                    "co.order_id = main_table.entity_id",
                    [
                       //'created_at' => 'co.created_at'
			           // 'created_at' => 'co.dispatched_on'
                    ]
                );
 
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
			//echo "<pre>";print_r($where);

		   foreach ($where as $key => $cond) {
				$condnew = str_replace('`dispatched_on`', 'co.created_at', $cond);
				$where[$key] = str_replace('`created_at`', 'main_table.created_at', $condnew);
            }
			
		 
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
			$collection->getSelect()->distinct(true);
 
           // echo $collection->getSelect()->__toString();die;
 
 
        }
        return $collection;
 
 
    }
 
 
}