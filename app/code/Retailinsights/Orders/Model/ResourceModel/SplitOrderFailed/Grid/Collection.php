<?php
namespace Retailinsights\Orders\Model\ResourceModel\SplitOrderFailed\Grid;

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
	protected $variable;
 
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
		\Magento\Variable\Model\Variable $variable,
		$mainTable = 'sales_order_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    )
    {
        $this->variable = $variable;
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    protected function _initSelect()
        {
            $this->addFilterToMap('increment_id', 'main_table.increment_id');
            $this->addFilterToMap('created_at', 'main_table.created_at');
            $this->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
            $this->addFilterToMap('store_id', 'main_table.store_id');
            $this->addFilterToMap('grand_total', 'main_table.grand_total');
            $this->addFilterToMap('status', 'main_table.status');
            $this->addFilterToMap('customer_email', 'main_table.customer_email');
            $this->addFilterToMap('total_refunded', 'main_table.total_refunded');
			$this->addFilterToMap('location_code', 'schools_registered.location_code');
			$this->addFilterToMap('schoolhub_name', 'centralbooks_schoolhub_schoolhub.schoolhub_name');
			$this->addFilterToMap('school_name', 'sales_order.school_name');
			
			
            parent::_initSelect();
        }
 
    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('sales_order');
        $secondTable = $this->getTable('sales_order_item');
        $thirdTable = $this->getTable('sales_order_address');
		$fourthTable = $this->getTable('schools_registered');
		$fifthTable = $this->getTable('centralbooks_schoolhub_schoolhub');
        $this->addFieldToFilter('status', array('nin' => array('order_split')));
		$cbo_assign_shippment_table = $this->getTable('cbo_assign_shippment');
		//$this->addFieldToFilter('status', array('nin' => array('processing','complete','dispatched_to_courier'))); 
		
        $startDate = '';
		$endDate = '';
		
		$startDatevalue = $this->variable->loadByCode('duplicate_order_startdate', 'admin');
		$startDate = $startDatevalue->getPlainValue();
		$endDatevalue = $this->variable->loadByCode('duplicate_order_enddate', 'admin');
		$endDate = $endDatevalue->getPlainValue();

		if(empty($endDate)) {
		   $endDate = date("Y-m-d h:i:s");
		}

		//$startDate = date('Y-m-d h:i:s', strtotime('-2 month'));

         $this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = sales_order.entity_id', ['student_name','roll_no','school_name','school_code','location_code','parent_split_order','product_purchased','shipsy_reference_numbers','shipsy_tracking_url','package_type','delivery_amount','order_multiple_status']);
		/*$this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = sales_order.entity_id', ['student_name','roll_no','school_name','school_code','parent_split_order','package_type','delivery_amount']);
        //$this->getSelect()->joinLeft($secondTable,'main_table.entity_id = sales_order_item.order_id', ['name', 'sku', 'order_id']);
		//$this->getSelect()->where('sales_order_item.parent_item_id IS NULL');
        $this->getSelect()->joinLeft($secondTable,'main_table.entity_id = sales_order_item.order_id',  array(
            'sku'  => new \Zend_Db_Expr('group_concat(DISTINCT sales_order_item.sku SEPARATOR ",")'),
            'order_id'  => new \Zend_Db_Expr('group_concat(DISTINCT sales_order_item.order_id SEPARATOR ",")'),
            'name' => new \Zend_Db_Expr('group_concat(DISTINCT sales_order_item.name SEPARATOR ", ")'),
            ))->where('sales_order_item.parent_item_id IS NULL');*/

        $this->getSelect()->joinLeft($thirdTable, "main_table.entity_id = 
        sales_order_address.parent_id AND sales_order_address.address_type = 'billing'", 
          ['telephone','postcode']);

		$this->getSelect()->joinLeft($fourthTable, "sales_order.school_code = 
        schools_registered.school_code", ['location_code', 'add_schoolhub']);

		$this->getSelect()->joinLeft($cbo_assign_shippment_table, "sales_order.entity_id = 
        cbo_assign_shippment.order_id", ['cbo_assign_shippment.tracking_number', 'dispatched_on' =>'cbo_assign_shippment.created_at']);

		$this->getSelect()->joinLeft($fifthTable, "schools_registered.add_schoolhub = 
        centralbooks_schoolhub_schoolhub.schoolhub_id", ['schoolhub_name']);
        $this->addFieldToFilter('sales_order.order_multiple_status', array('in' => 'success'));

	    if(!empty($startDate)) {
		$this->addFieldToFilter('main_table.created_at', array('from'=>$startDate, 'to'=>$endDate));
		}

        $this->getSelect()->group('main_table.entity_id');
		//echo $this->getSelect()->__toString(); die;
        parent::_renderFiltersBefore();
    }
}
