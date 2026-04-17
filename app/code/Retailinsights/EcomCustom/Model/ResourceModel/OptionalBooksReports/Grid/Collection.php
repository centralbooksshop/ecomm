<?php
namespace Retailinsights\EcomCustom\Model\ResourceModel\OptionalBooksReports\Grid;


use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;
 

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    protected $helper;
 
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    protected function _initSelect()
        {
            $this->addFilterToMap('increment_id', 'main_table.increment_id');
            $this->addFilterToMap('created_at', 'main_table.created_at');
            //$this->addFilterToMap('store_id', 'main_table.store_id');
            //$this->addFilterToMap('status', 'main_table.status');
            $this->addFilterToMap('customer_email', 'main_table.customer_email');
            $this->addFilterToMap('school_name', 'main_table.school_name');
			$this->addFilterToMap('school_code', 'main_table.school_code');
			$this->addFilterToMap('roll_no', 'main_table.roll_no');
			$this->addFilterToMap('student_name', 'main_table.student_name');
			//$this->addFilterToMap('name', 'sales_order_item.name');
			
            parent::_initSelect();
			
        }
 
    protected function _renderFiltersBefore()
    {
        $sales_orderTable = $this->getTable('sales_order');
        $sales_order_item = $this->getTable('sales_order_item');
        $thirdTable = $this->getTable('sales_order_address');
		$this->addFieldToFilter('status', array('nin' => array('order_split')));
	    $this->addFieldToFilter('status', array('in' => array('processing','complete','dispatched_to_courier','order_delivered','order_not_delivered')));

	
		$startDate = date('Y-m-d h:i:s', strtotime('-11 month'));
		$endDate = date("Y-m-d h:i:s");		

       /* $this->getSelect()->joinLeft($sales_orderTable, 'main_table.entity_id = sales_order.entity_id', ['customer_name' => new \Zend_Db_Expr('CONCAT(sales_order.customer_firstname, " ", sales_order.customer_lastname)')
			]); */
		/*$this->getSelect()->joinLeft($sales_order_item,'main_table.entity_id = sales_order_item.order_id', ['name' => new \Zend_Db_Expr('group_concat(sales_order_item.name SEPARATOR ", ")')])->where('sales_order_item.parent_item_id IS NOT NULL');*/

        $this->getSelect()->joinLeft($thirdTable, "main_table.entity_id = 
        sales_order_address.parent_id AND sales_order_address.address_type = 'billing'", 
          ['telephone','firstname','lastname']);

		$this->addFieldToFilter('main_table.created_at', array('from'=>$startDate, 'to'=>$endDate));

       
        $this->getSelect()->group('main_table.entity_id');
		//echo $this->getSelect()->__toString();die;
        parent::_renderFiltersBefore();
    }
}