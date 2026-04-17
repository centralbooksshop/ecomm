<?php
namespace Retailinsights\ProcessCBOOrders\Model\ResourceModel\Reason;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = "id";

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
    }

    /**
     * Initalize collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Retailinsights\ProcessCBOOrders\Model\Reason::class,
            \Retailinsights\ProcessCBOOrders\Model\ResourceModel\Reason::class
        );
        $this->_map["fields"]["id"] = "main_table.id";
    }

    /**
     * Add store Filter to the collection.
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
     * Set Reason Data.
     *
     * @param mixed $condition UPDATE WHERE clause(s).
     * @param array $attributeData Column-value pairs.
     * @return int The number of affected rows.
     */
    public function setReasonData($condition, $attributeData)
    {
        return $this->getConnection()->update(
            $this->getTable("deliveryboy_reason"),
            $attributeData,
            $where = $condition
        );
    }
}
