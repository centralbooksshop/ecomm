<?php
namespace Retailinsights\DelhiveryCustom\Model\ResourceModel\PendingDelhiveryOrders\Grid;


use Magento\Framework\View\Element\UiComponent\DataProvider\Document as BlogModel;
use Retailinsights\DelhiveryCustom\Model\ResourceModel\PendingDelhiveryOrders\Collection as OrderCollection;

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
        'so.entity_id = main_table.entity_id',
        ['main_table.entity_id','so.billing_name']
    )
    ->joinLeft(
        ['soi' => $this->getTable('sales_order_item')],
        'soi.order_id = main_table.entity_id',
        ['soi.name']
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
        $this->addFilterToMap('increment_id','main_table.increment_id');
        $this->addFilterToMap('status','main_table.status');
        $this->addFilterToMap('created_at','main_table.created_at');
        $this->addFilterToMap('shipment_type', 'main_table.shipment_type');
        $this->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
        $this->addFilterToMap('name', 'soi.name');
    }

    public function _renderFiltersBefore()
    {
        $this->addFieldToFilter('shipment_type', array('in' => array('delhivery')));
        $this->addFieldToFilter('status', array('nin' => array('order_delivered','dispatched_to_courier')));
        // $this->addFieldToFilter('tracking_title', array('in' => array('fedex')));
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