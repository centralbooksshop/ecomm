<?php
namespace Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\Grid;


use Magento\Framework\View\Element\UiComponent\DataProvider\Document as BlogModel;
use Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\Collection as OrderCollection;

class Collection extends OrderCollection implements \Magento\Framework\Api\Search\SearchResultInterface
{
     /**
     * @var AggregationInterface
     */
    protected $aggregations;
 
    protected function _initSelect()
{
    parent::_initSelect();
    $this->getSelect()
    ->joinLeft(
        ['so' => $this->getTable('sales_order_grid')],
        'so.entity_id = main_table.order_id',
        ['main_table.id','so.increment_id','so.status','so.customer_email','so.base_grand_total','so.payment_method','so.billing_name','so.shipping_address','so.created_at', 'so.shipping_name']
        )
        ->joinLeft(
        ['sop' => $this->getTable('sales_order')],
        'sop.entity_id = main_table.order_id',
        ['sop.shipping_address_id','sop.student_name','sop.roll_no' ,'sop.school_name' ,'sop.school_code', 'sop.product_purchased']
        )
        ->joinLeft(
            ['soa' => $this->getTable('sales_order_address')],
            'soa.entity_id=sop.shipping_address_id',
            ['soa.postcode','soa.telephone']
            )
        ->joinLeft(
            ['d' => $this->getTable('cboshipping_autodrivers')],
            'd.id = main_table.driver_id',
            ['d.driver_name', 'd.driver_mobile','main_table.created_at','d.auto_number']
        );        
}

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param string $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|string|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
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
        $this->addFilterToMap('increment_id','so.increment_id');
        $this->addFilterToMap('status','so.status');
        $this->addFilterToMap('created_at','main_table.created_at');

    }
    public function _renderFiltersBefore()
    {
        $this->addFieldToFilter('status', array('in' => array('order_not_delivered','order_delivered','dispatched_to_courier')));
        $this->addFieldToFilter('driver_id', array('null' => true));
		$this->addFieldToFilter('deliveryboy_id', array('null' => true));
        parent::_renderFiltersBefore();
    }

 
    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }
 
    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }
 
    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }
 
    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }
 
    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }
 
    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }
 
    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    // public function getRowUrl($row){
    //         return $row;
    // }
}
