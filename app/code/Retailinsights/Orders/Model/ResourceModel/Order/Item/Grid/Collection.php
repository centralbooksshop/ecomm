<?php
namespace Retailinsights\Orders\Model\ResourceModel\Order\Item\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Zend_Db_Expr;

class Collection extends SearchResult
{
    protected function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Magento\Sales\Model\ResourceModel\Order\Item::class
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToSelect([
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
			'dispatch_status',
			'partial_delivery_date',
            'acknowledgement_upload'
        ]);
    }

    protected function _renderFiltersBefore()
    {
        $salesOrderTable = $this->getTable('sales_order');

        $this->getSelect()->joinLeft(
            ['so' => $salesOrderTable],
            'main_table.order_id = so.entity_id',
            ['school_name', 'product_purchased', 'order_multiple_status']
        );

        $this->addFieldToFilter('so.status', ['in' => ['assigned_to_picker','processing', 'complete', 'dispatched_to_courier', 'order_delivered']]);
        $this->addFieldToFilter('so.order_multiple_status', ['null' => true]);
        $this->getSelect()->where('main_table.given_options IN (?)', [1, 2]);

        /*$orderLineExpr = new Zend_Db_Expr(
            "IF(main_table.delivery_date IS NOT NULL, 'Delivered to school', 'New Order')"
        );

		$orderLineExpr = new Zend_Db_Expr(
				"IF(main_table.delivery_date IS NOT NULL 
					 AND main_table.dispatch_date IS NOT NULL 
					 AND main_table.dispatch_date != '0000-00-00 00:00:00',
					'Delivered to school', 
					'New Order'
				)"
			);

			$orderLineExpr = new \Zend_Db_Expr(
				"IF(
					(
						main_table.delivery_date IS NOT NULL
						AND (
							(main_table.dispatch_date IS NOT NULL AND main_table.dispatch_date != '0000-00-00 00:00:00')
							OR (main_table.delivery_date IS NOT NULL AND main_table.delivery_date != '0000-00-00 00:00:00')
						)
					),
					'Delivered to school',
					'New Order'
				)"
			);


			$orderLineExpr = new \Zend_Db_Expr(
				"IF(
					(
						main_table.delivery_date IS NOT NULL
						AND (
							(main_table.dispatch_date IS NOT NULL AND main_table.dispatch_date != '0000-00-00 00:00:00')
						)
						AND (main_table.delivery_date IS NOT NULL AND main_table.delivery_date != '0000-00-00 00:00:00')
					),
					'Delivered to school',
					'New Order','New Order'
				)"
			);*/


			$orderLineExpr = new \Zend_Db_Expr(
				"IF(
					(
						main_table.delivery_date IS NOT NULL
						AND main_table.delivery_date != '0000-00-00 00:00:00'
						AND main_table.dispatch_date IS NOT NULL
						AND main_table.dispatch_date != '0000-00-00 00:00:00'
					),
					'Delivered to school',
					'New Order'
				)"
			);

			$confirmBucketExpr = new \Zend_Db_Expr("
				IF(
					main_table.delivery_date IS NOT NULL
					AND main_table.delivery_date != '0000-00-00 00:00:00',
					'Confirmed',
					'Not Confirmed'
				)
			");

            $this->getSelect()->columns([
				// total in this bucket (Confirmed OR Not Confirmed)
				'total_name_count' => new \Zend_Db_Expr('COUNT(main_table.item_id)'),

				// counts inside the bucket (only one of these will be > 0)
				'confirmed_name_count' => new \Zend_Db_Expr("
					SUM(
						CASE
							WHEN main_table.delivery_date IS NOT NULL
								 AND main_table.delivery_date != '0000-00-00 00:00:00'
							THEN 1
							ELSE 0
						END
					)
				"),
				'not_confirmed_name_count' => new \Zend_Db_Expr("
					SUM(
						CASE
							WHEN main_table.delivery_date IS NULL
								 OR main_table.delivery_date = '0000-00-00 00:00:00'
							THEN 1
							ELSE 0
						END
					)
				"),

				// this identifies which bucket the row represents
				'item_confirm_status' => $confirmBucketExpr,

				'all_item_ids'           => new \Zend_Db_Expr('GROUP_CONCAT(main_table.item_id)'),
				'order_line_type'        => $orderLineExpr,
				'dispatch_total_qty'     => new \Zend_Db_Expr('SUM(main_table.qty_ordered)'),
				'dispatch_date'          => new \Zend_Db_Expr("
					IF(main_table.partial_delivery_date IS NOT NULL 
						AND main_table.partial_delivery_date != '0000-00-00 00:00:00',
						NULL,
						MAX(main_table.dispatch_date)
					)
				"),
				'partially_status'        => new \Zend_Db_Expr("
					IF(main_table.partial_delivery_date IS NOT NULL 
						AND main_table.partial_delivery_date != '0000-00-00 00:00:00',
						'Partially Dispatched',
						''
					)
				"),
				'acknowledgement_upload' => new \Zend_Db_Expr("
					IF(main_table.acknowledgement_upload IS NOT NULL,
						CONCAT('/pub/media/acknowledgement_upload/', main_table.acknowledgement_upload),
						NULL
					)
				")
			]);



		/*$this->getSelect()->columns([
		'total_name_count'       => new \Zend_Db_Expr('COUNT(main_table.item_id)'),
		'all_item_ids'           => new \Zend_Db_Expr('GROUP_CONCAT(main_table.item_id)'),
		'order_line_type'        => $orderLineExpr,
		'dispatch_total_qty'     => new \Zend_Db_Expr('SUM(main_table.qty_ordered)'),
		//'dispatch_date'          => new \Zend_Db_Expr('MAX(main_table.dispatch_date)'),
		 // Keep valid datetime in dispatch_date
		'dispatch_date'          => new \Zend_Db_Expr("
			IF(main_table.partial_delivery_date IS NOT NULL 
				AND main_table.partial_delivery_date != '0000-00-00 00:00:00',
				NULL,
				MAX(main_table.dispatch_date)
			)
		"),
		
		// Add a readable text column
		'dispatch_status'        => new \Zend_Db_Expr("
			IF(main_table.partial_delivery_date IS NOT NULL 
				AND main_table.partial_delivery_date != '0000-00-00 00:00:00',
				'Partially Dispatched',
				''
			)
		"),

		'acknowledgement_upload' => new \Zend_Db_Expr("
			IF(main_table.acknowledgement_upload IS NOT NULL,
				CONCAT('/pub/media/acknowledgement_upload/', main_table.acknowledgement_upload),
				NULL
			)
		")
	   ]);*/

        /*$this->getSelect()->group([
            'main_table.name',
            'main_table.given_options',
            'so.product_purchased',
            //'main_table.delivery_date',
			$orderLineExpr
        ]);*/

		$this->getSelect()->group([
			'main_table.name',
			'main_table.given_options',
			'so.product_purchased',
			$orderLineExpr,
			$confirmBucketExpr
		]);


        parent::_renderFiltersBefore();
    }

    /**
     * Fix pagination count issue
     */
    /*protected function _getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();

        // Remove ORDER, LIMIT, OFFSET
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);

        // Count distinct rows based on the same GROUP BY logic
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->columns(new Zend_Db_Expr(
            'COUNT(DISTINCT CONCAT(main_table.name, "_", main_table.given_options, "_", so.product_purchased, "_", main_table.delivery_date))'
        ));

        return $countSelect;
    }*/
}
