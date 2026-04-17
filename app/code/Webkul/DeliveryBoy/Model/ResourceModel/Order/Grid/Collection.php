<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model\ResourceModel\Order\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection as OrderCollection;

class Collection extends OrderCollection implements SearchResultInterface
{
    /**
     * @var \Magento\Framework\Api\Search\AggregationInterface
     */
    protected $_aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        string $mainTable,
        string $eventPrefix,
        string $eventObject,
        string $resourceModel,
        string $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
		$this->addFilterToMap(
            'id',
            'main_table.id'
        );
		$this->addFilterToMap('increment_id','main_table.increment_id');
        $this->addFilterToMap('alternate_delivery','main_table.alternate_delivery');
        $this->addFilterToMap('package_items','main_table.package_items');
        $this->addFilterToMap('delivery_amount','main_table.delivery_amount');
        $this->addFilterToMap('comments','main_table.comments');
		$this->addFilterToMap('created_at','cboTable.created_at');
		$this->addFilterToMap('deliveryboy_name','deliveryboyTable.name');
		$this->addFilterToMap('email','quoteTable.customer_email');
		$this->addFilterToMap('deliveryboy_mobile_number','deliveryboyTable.mobile_number');
		$this->addFilterToMap('deliveryboy_vehicle_number','deliveryboyTable.vehicle_number');
		$this->addFilterToMap('partner_type','deliveryboyTable.partner_type');
		$this->addFilterToMap('student_name','orderTable.student_name');
		$this->addFilterToMap('roll_no','orderTable.roll_no');
		$this->addFilterToMap('school_name','orderTable.school_name');
		$this->addFilterToMap('school_code','orderTable.school_code');

	    //$select = $this->getSelect();
		//$this->applyCustomFilters($select);
		//echo $sql = $select->assemble();
    }

	 public function _renderFiltersBefore()
    {
      $this->addFieldToFilter('main_table.order_status', array('in' => array('order_not_delivered','order_delivered','dispatched_to_courier')));
      $this->addFieldToFilter('tracking_title', array('null' => true));
	  $this->addFieldToFilter('driver_id', array('null' => true));
	  

		$select = $this->getSelect();
		$this->applyCustomFilters($select);
		$sql = $select->assemble();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminuser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getUsername();
		if($adminuser !='admin') {
         $deliveryboypartner_id = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getDeliveryboyPartnerType();
         $this->getSelect()->where("partner_type = '$deliveryboypartner_id'");
		}
	    //echo $this->getSelect()->__toString(); die;
        parent::_renderFiltersBefore();
    }

    /**
     * Get Aggregations.
     *
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * Set Aggregations.
     *
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return self
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * Get all ids.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get Search criteria.
     *
     * @return null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set Search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return self
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get Total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set TOtal count.
     *
     * @param int $totalCount
     * @return self
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set Items.
     *
     * @param array $items
     * @return self
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Reset Select.
     *
     * @param Select $select
     * @return void
     */
    public function applyCustomFilters($select)
    {
		$this->joinOrderTable($select);
		$this->leftJoinOrderGridTable($select);
        $this->leftJoinQuoteTable($select);
		$this->leftJoinCBOTable($select);
        $this->leftJoinDeliveryboyTable($select);
	//	$this->leftJoinOrderAddressTable($select);
		$this->leftJoinOrderTransactionTable($select);
		$this->leftJoinOrderTable($select);
		$this->leftJoinOrderAddressTable($select);
		$sql = $select->assemble();
        $select->reset();
        $select->from(
            ["main_table" => new \Zend_Db_Expr("($sql)")],
            new \Zend_Db_Expr("*")
        );
    }


    /**
     * Join Quote Table Left.
     *
     * @param Select $select
     * @return void
     */

	  public function leftJoinOrderGridTable($select)
      {
        $OrderGridTable = $this->getTable('sales_order_grid');
        $select->joinLeft(
            $OrderGridTable . ' as OrderGridTable',
            'main_table.order_id = OrderGridTable.entity_id',
            [
                //'increment_id' => 'OrderGridTable.increment_id',
			    //'status' => 'OrderGridTable.status',
			'customer_email' => 'OrderGridTable.customer_email',
				'base_grand_total' => 'OrderGridTable.base_grand_total',
				'payment_method' => 'OrderGridTable.payment_method',
				'billing_name' => 'OrderGridTable.payment_method',
				'shipping_address' => 'OrderGridTable.shipping_address',
				'shipping_name' => 'OrderGridTable.shipping_name',
				//'created_at' => 'OrderGridTable.created_at',
            ]
		
        );
      }

    public function leftJoinOrderTable($select)
      {
        $OrderTable = $this->getTable('sales_order');
        $select->joinLeft(
            $OrderTable . ' as OrderTable',
            'main_table.order_id = OrderTable.entity_id',
            [
                        'product_purchased' => 'OrderTable.product_purchased',
            ]

        );
      }

    public function leftJoinOrderAddressTable($select)
      {
        $OrderAddressTable = $this->getTable('sales_order_address');
        $select->joinLeft(
            $OrderAddressTable . ' as OrderAddressTable',
            'main_table.order_id = OrderAddressTable.parent_id',
            [
                        'telephone' => 'OrderAddressTable.telephone',
            ]

	);
	$select->where('OrderAddressTable.address_type = ?', 'shipping');
    } 


    public function leftJoinQuoteTable($select)
    {
        $quoteTable = $this->getTable('quote');
        $select->joinLeft(
            $quoteTable . ' as quoteTable',
            'orderTable.quote_id = quoteTable.entity_id',
            [
                'firstname' => 'IFNULL(quoteTable.customer_firstname, "GUEST")',
                'lastname' => 'quoteTable.customer_lastname',
			    'email' => 'quoteTable.customer_email'

            ]
        );
        return $select;
    }

	 public function leftJoinCBOTable($select)
    {
        $cboTable = $this->getTable('cbo_assign_shippment');
        $select->joinLeft(
            $cboTable . ' as cboTable',
            'main_table.order_id = cboTable.order_id',
            [
                'created_at' => 'cboTable.created_at',
			    'tracking_title' => 'cboTable.tracking_title'
            ]
        );
        //return $select;
    }

	

    /**
     * Left Join Deliveryboy table.
     *
     * @param Select $select
     * @return void
     */
    public function leftJoinDeliveryboyTable($select)
    {
        $select->joinLeft(
            $this->getTable('deliveryboy_deliveryboy') . ' as deliveryboyTable',
            'main_table.deliveryboy_id = deliveryboyTable.id',
            [
	       'deliveryboy_name' => 'deliveryboyTable.name',
		       'deliveryboy_mobile_number' => 'deliveryboyTable.mobile_number',
		       'deliveryboy_vehicle_number' => 'deliveryboyTable.vehicle_number',
		       'partner_type' => 'deliveryboyTable.partner_type'
			]
        );
        $this->_eventManager->dispatch(
            'wk_deliveryboy_assigned_order_collection_apply_filter_event',
            [
                'deliveryboy_order_collection' => $this,
                'collection_table_name' => 'main_table',
                'owner_id' => 0,
            ]
        );
        return $select;
    }
}
