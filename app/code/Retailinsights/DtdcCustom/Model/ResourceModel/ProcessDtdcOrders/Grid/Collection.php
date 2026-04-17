<?php
namespace Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Retailinsights\DtdcCustom\Model\ProcessDtdcOrders::class,
            \Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders::class
        );
    }
     public function addFieldToFilter($field, $condition = null)
	{
		$this->setFlag('ui_filter_applied', true);
		return parent::addFieldToFilter($field, $condition);
	}


    protected function _initSelect()
    {
        parent::_initSelect();

		$this->addFilterToMap('increment_id', 'so.increment_id');
		$this->addFilterToMap('customer_email', 'so.customer_email');
		$this->addFilterToMap('status', 'so.status');
		$this->addFilterToMap('created_at', 'so.created_at');
		$this->addFilterToMap('school_code', 'sop.school_code');
		$this->addFilterToMap('billing_name', 'so.billing_name');
        // Ensure correct main table
        $this->setMainTable('cbo_assign_shippment');

        // Join other tables
        $this->getSelect()
            ->joinLeft(
                ['so' => $this->getTable('sales_order_grid')],
                'so.entity_id = main_table.order_id',
                [
                    'increment_id',
                    'status',
                    'billing_name',
                    'customer_email',
                    'base_grand_total',
                    'payment_method',
                    'main_table.created_at'
                ]
            )
            ->joinLeft(
                ['sop' => $this->getTable('sales_order')],
                'sop.entity_id = main_table.order_id',
                [
                    'shipping_address_id',
                    'student_name',
                    'roll_no',
                    'school_name',
                    'school_code',
                    'shipment_type'
                ]
            )
            ->joinLeft(
                ['soa' => $this->getTable('sales_order_address')],
                'soa.entity_id = sop.shipping_address_id',
                [
                    'postcode',
                    'telephone'
                ]
            );

        // Select specific columns safely
        $this->getSelect()->columns([
            'main_table.id',
            'main_table.tracking_title',
            'main_table.tracking_number'
        ]);

        // Default filters: only DTDC + current month
        //$startOfMonth = date('Y-m-01 00:00:00');
		$startOfMonth = '2025-10-01 00:00:00';
        $endOfMonth   = date('Y-m-t 23:59:59');

        $this->addFieldToFilter('so.status', ['in' => ['order_not_delivered','order_delivered', 'dispatched_to_courier']]);
        $this->addFieldToFilter('main_table.tracking_title',['in' => ['DTDC', 'Delhivery','Elasticrun','Amazon','SMCS']]);
        $this->addFieldToFilter('so.created_at', ['from' => $startOfMonth, 'to' => $endOfMonth]);
    }
}
