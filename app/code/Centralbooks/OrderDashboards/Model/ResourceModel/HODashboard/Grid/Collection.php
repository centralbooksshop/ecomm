<?php
namespace Centralbooks\OrderDashboards\Model\ResourceModel\HODashboard\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    protected $helper;
	public $variable;
	public $request;
	protected $timezoneInterface;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Variable\Model\Variable $variable,
		TimezoneInterface $timezoneInterface,
        $mainTable = 'sales_order_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    )
    {
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
		$this->request = $request;
		$this->variable = $variable;
		$this->timezoneInterface = $timezoneInterface;
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
		$this->addFilterToMap('location_code', 'sales_order.location_code');
		$this->addFilterToMap('schoolhub_name', 'centralbooks_schoolhub_schoolhub.schoolhub_name');
		$this->addFilterToMap('school_name', 'sales_order.school_name');
		
		
		parent::_initSelect();
	}
 
    protected function _renderFiltersBefore()
    {
        $params = $this->request->getParams();
		//echo '<pre>';print_r($params);die;
		$status_param = $this->request->getParam('status');
		$time_param = $this->request->getParam('time');
		$payment_method_param = $this->request->getParam('payment_method');

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$objData = $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
		$current_date = $this->timezoneInterface->date()->format('Y-m-d');
		$endDate = $objData->date('Y-m-d', strtotime($current_date." +1 days"));

		$highestTime = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('cbo/payments/highest_orders_time');
		$sales_order_table = $this->getTable('sales_order');
		$sales_order_address_table = $this->getTable('sales_order_address');
		$schools_registered_table = $this->getTable('schools_registered');
		$schoolhub_table = $this->getTable('centralbooks_schoolhub_schoolhub');
		$cbo_assign_shippment_table = $this->getTable('cbo_assign_shippment');
        $sales_order_payment_table = $this->getTable('sales_order_payment');
		$this->addFieldToFilter('status', array('nin' => array('order_split')));
		//$this->addFieldToFilter('status', array('in' => array('processing','complete','dispatched_to_courier')));

		$this->getSelect()->joinLeft($sales_order_table, 'main_table.entity_id = sales_order.entity_id', ['student_name','roll_no','school_name','school_code','location_code','parent_split_order','product_purchased','shipsy_reference_numbers','shipsy_tracking_url','package_type','delivery_amount']);
	
		$this->getSelect()->joinLeft($sales_order_address_table, "main_table.entity_id = 
		sales_order_address.parent_id AND sales_order_address.address_type = 'billing'", 
		  ['telephone','postcode']);

		$this->getSelect()->joinLeft($schools_registered_table, "sales_order.school_code = 
		schools_registered.school_code", ['add_schoolhub']);

		$this->getSelect()->joinLeft($cbo_assign_shippment_table, "sales_order.entity_id = 
		cbo_assign_shippment.order_id", ['cbo_assign_shippment.tracking_number', 'dispatched_on' =>'cbo_assign_shippment.created_at']);

		$this->getSelect()->joinLeft($schoolhub_table, "schools_registered.add_schoolhub = 
		centralbooks_schoolhub_schoolhub.schoolhub_id", ['schoolhub_name']);

		$this->getSelect()->joinLeft($sales_order_payment_table, "main_table.entity_id = 
		sales_order_payment.parent_id", ['method']);

		if(!empty($highestTime)) {
		   //$this->addFieldToFilter('main_table.created_at', array('from'=>$startDate, 'to'=>$endDate));
			$date1 = (new \DateTime())->modify($highestTime);
			$this->addFieldToFilter('main_table.created_at', ['gteq' => $date1->format('Y-m-d h:i:s')]);
		}

		$this->getSelect()->group('main_table.entity_id');
		//echo $this->getSelect()->__toString(); die;
        parent::_renderFiltersBefore();
    }
}