<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder\Grid;

class Collection extends \Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder\Collection implements
    \Magento\Framework\Api\Search\SearchResultInterface
{

    
    /**
     * Aggregation
     *
     * @var \Magento\Framework\Search\AggregationInterface
     */
    protected $_aggregation;
    
    /**
     * constructor
     *
     * @ param \magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @ param \Psr\Log\LoggerInterface $logger
     * @ param \magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchstrategy
     * @ param \magento\Framework\Event\ManagerInterface $eventManager
     * @ param $mainTable
     * @ param $eventPrefix
     * @ param $eventObject
     * @ param $resourceModel
     * @ param $model
     * @ param $connection
     *
     * @ param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchstrategy,
        \magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchstrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }


	 protected function _renderFiltersBefore()
     {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminuser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getUsername();
		if($adminuser !='admin') {
         $pickupstore = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getPickupstore();
         $this->getSelect()->where("main_table.store_name = '$pickupstore'");
		}
        //echo $this->getSelect()->__toString(); die;
        parent::_renderFiltersBefore();
     }
    /**
     *
     * @ return \Magento\Framework\Search\AggregationInterface
     *
     */
    
    public function getAggregations()
    {
        return $this->_aggregations;
    }
    
    /**
     * @ param \Magento\Framework\Api\Search\SearchResultInterface
     * @ return $this
     *
     */
    
    public function setAggregations($aggregations)
    {
        $_aggregations = $aggregations;
    }
    
    /**
     * Retrive all ids for collection
     * Backward Compatibility with EAV collection
     * @param int $limit
     * @param int $offset
     * @return array
     *
     */
    
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }
    
    /**
     * Get Search Criteria.
     *
     * @ return \Magento\Framework\Api\SearchCriteriaInterface |null
     *
     */
    public function getSearchCriteria()
    {
        return null;
    }
    
    /**
     * Set Search Criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }
    
    /**
     * Get total count.
     *
     * @return int
     *
     */
    
    public function getTotalCount()
    {
        return $this->getSize();
    }
    
    /**
     * set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }
    
    /**
     * Set item list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @retuen Sthis
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    
    public function setItems(array $items = null)
    {
        return $this;
    }
}
