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
namespace Webkul\DeliveryBoy\Model\ResourceModel\OrderTransaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = "entity_id";

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManager,
            $connection,
            $resource
        );
        $this->joinDeliveryboyOrderCollection();
        $this->treatCollectionForFilters();
    }

    /**
     * Initialize collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\DeliveryBoy\Model\OrderTransaction::class,
            \Webkul\DeliveryBoy\Model\ResourceModel\OrderTransaction::class
        );
        $this->_map["fields"]["entity_id"] = "main_table.entity_id";
    }

    /**
     * Add Store Filter.
     *
     * @param int|null $store
     * @param bool $withAdmin
     * @return self
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag("store_filter_added")) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Join delvieryboy order collection
     *
     * @param Select $select
     * @return void
     */
    public function joinDeliveryboyOrderCollection($select = null)
    {
        $select = $select ?: $this->getSelect();
        $quoteTable = $this->getTable('deliveryboy_orders');
        $select->join(
            $quoteTable . ' as deliveryboyOrderTable',
            'main_table.deliveryboy_order_id = deliveryboyOrderTable.id',
            [
                'order_increment_id' => 'deliveryboyOrderTable.increment_id',
                'order_id' => 'deliveryboyOrderTable.order_id'
            ]
        );
        return $select;
    }

    /**
     * Format Sql.
     *
     * @param Select $select
     * @return void
     */
    public function treatCollectionForFilters($select = null)
    {
        $select = $select ?: $this->getSelect();
        $sql = $select->assemble();
        $select->reset();
        $select->from(
            ["main_table" => new \Zend_Db_Expr("($sql)")],
            new \Zend_Db_Expr("*")
        );
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
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
