<?php
namespace Retailinsights\Orders\Model\ResourceModel\Order\Item\Details;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected $nameFilter = null;

    protected function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Magento\Sales\Model\ResourceModel\Order\Item::class
        );
    }

    /**
     * Set URL name filter from DataProvider
     */
    public function setNameFilter($name)
    {
        $this->nameFilter = $name;
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToSelect([
            'item_id',
            'order_id',
            'sku',
            'name',
            'qty_ordered',
            'given_options',
            'base_original_price',
            'given_option_updated_at',
            'dispatch_total_qty',
            'dispatch_date',
			'delivery_date',
			'delivery_status',
			'partial_delivery_date',
            'acknowledgement_upload'
        ]);
    }

    protected function _renderFiltersBefore()
    {
        
		$salesOrderTable = $this->getTable('sales_order');
        $customerEntityTable = $this->getTable('customer_entity');
        $customerAddressTable = $this->getTable('customer_address_entity');

        // Join sales_order table
        $this->getSelect()->joinLeft(
            ['so' => $salesOrderTable],
            'main_table.order_id = so.entity_id',
            [
                'school_name',
                'product_purchased',
                'student_name',
                'roll_no',
                'increment_id',
                'customer_email',
                new \Zend_Db_Expr("CONCAT(so.customer_firstname, ' ', so.customer_lastname) AS customer_name"),
                'created_at AS order_date',
			    'order_multiple_status'
            ]
        );

        // Join customer_entity to get mobile
        $this->getSelect()->joinLeft(
            ['ce' => $customerEntityTable],
            'so.customer_id = ce.entity_id',
            ['customer_mobile' => 'mobile_number']
        );

        // Join default shipping address
        $this->getSelect()->joinLeft(
            ['caes' => $customerAddressTable],
            'ce.default_shipping = caes.entity_id',
            [
                'shipping_address' => new \Zend_Db_Expr(
                    "CONCAT_WS(', ', caes.street, caes.city, caes.region_id, caes.postcode, caes.country_id)"
                ),
                'shipping_mobile' => 'telephone'
            ]
        );

        // Filter by date range (last 12 months)
        $endDate = date("Y-m-d H:i:s");
        $startDate = date('Y-m-d H:i:s', strtotime('-12 month'));
        //$this->addFieldToFilter('main_table.created_at', ['from' => $startDate, 'to' => $endDate]);
		$this->addFieldToFilter('so.status',['in' => ['assigned_to_picker','processing','complete', 'dispatched_to_courier', 'order_delivered']]);
		$this->addFieldToFilter('so.order_multiple_status', array('null' => true));

        // Filter by given_options (1 or 2)
        $this->getSelect()->where('main_table.given_options IN (?)', [1, 2]);
		//$this->getSelect()->where('main_table.delivery_date IS NULL');

        // Apply name filter from DataProvider if set
        if ($this->nameFilter) {
            $this->getSelect()->where('main_table.name = ?', $this->nameFilter);
        }


        // Add total count per name
        /*$this->getSelect()->columns([
            'total_name_count' => new \Zend_Db_Expr('COUNT(main_table.item_id)')
        ]);*/

        // Group by name to avoid duplicates
        //$this->getSelect()->group('main_table.name');
		//echo $this->getSelect()->__toString();exit;
        parent::_renderFiltersBefore();
    }
}
