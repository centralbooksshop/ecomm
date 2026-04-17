<?php
declare(strict_types=1);

namespace Centralbooks\OrderDashboards\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\OrderDashboards\Model\Order::class,
            \Centralbooks\OrderDashboards\Model\ResourceModel\Order::class
        );
    }

	protected function _renderFiltersBefore()
    {
        
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$request = $objectManager->create('\Magento\Framework\App\Request\Http');
		$params = $request->getParams();
		//echo '<pre>';print_r($params);die;
		$status_param = $request->getParam('status');
		$time_param = $request->getParam('time');
		$payment_method_param = $request->getParam('payment_method');

		$highestTime = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('cbo/payments/highest_orders_time');
		$sales_order_table = $this->getTable('sales_order');
		$sales_order_address_table = $this->getTable('sales_order_address');
		$schools_registered_table = $this->getTable('schools_registered');
		$schoolhub_table = $this->getTable('centralbooks_schoolhub_schoolhub');
		$cbo_assign_shippment_table = $this->getTable('cbo_assign_shippment');
		$this->addFieldToFilter('main_table.status', array('nin' => array('order_split')));

		
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

		if(!empty($highestTime)) {
		   //$this->addFieldToFilter('main_table.created_at', array('from'=>$startDate, 'to'=>$endDate));
			$date1 = (new \DateTime())->modify($highestTime);
			$this->addFieldToFilter('main_table.created_at', ['gteq' => $date1->format('Y-m-d h:i:s')]);
		}

		if(!empty($status_param)) {
            $this->addFieldToFilter('main_table.status', array('in' => $status_param));
		}

		if(!empty($payment_method_param)) {
           $this->getSelect()->join(["sop" => "sales_order_payment"], 'main_table.entity_id = sop.parent_id',["sop.method"])->where('sop.method = ?', $payment_method_param);
		}
			
		$this->getSelect()->group('main_table.entity_id');
		//echo $this->getSelect()->__toString(); die;
        parent::_renderFiltersBefore();
    }
}

