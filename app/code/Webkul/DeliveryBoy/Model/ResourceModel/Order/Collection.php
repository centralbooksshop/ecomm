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
namespace Webkul\DeliveryBoy\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
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
    }

    /**
     * Initialize collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\DeliveryBoy\Model\Order::class,
            \Webkul\DeliveryBoy\Model\ResourceModel\Order::class
        );
        $this->_map["fields"]["id"] = "main_table.id";
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
     * Set Order Data.
     *
     * @param mixed $condition UPDATE WHERE clause(s).
     * @param array $attributeData Column-value pairs.
     * @return int The number of affected rows.
     */
    public function setOrderData($condition, $attributeData)
    {
        return $this->getConnection()->update(
            $this->getTable("deliveryboy_orders"),
            $attributeData,
            $where = $condition
        );
    }

    /**
     * Join sales order table.
     *
     * @param Select $select
     * @return void
     */
    public function joinOrderTable($select)
    {
        $orderTable = $this->getTable('sales_order');
        $select->join(
            $orderTable . ' as orderTable',
            'main_table.order_id = orderTable.entity_id',
            [
                'status' => 'orderTable.status',
		        'total' => 'orderTable.grand_total',
                'order_currency_code' => 'orderTable.order_currency_code',
			    'shipping_address_id' => 'orderTable.shipping_address_id',
			    'student_name' => 'orderTable.student_name',
			    'roll_no' => 'orderTable.roll_no',
			    'school_name' => 'orderTable.school_name',
			    'school_code' => 'orderTable.school_code',
            ]

		);
        return $select;
    }


	 
	 public function leftJoinOrderAddressTable($select)
     {
        $deliveryboyOrderAddress = $this->getTable('sales_order_address');
        $select->joinLeft(
            $deliveryboyOrderAddress . ' as deliveryboyOrderAddressTable',
            'orderTable.shipping_address_id = deliveryboyOrderAddressTable.entity_id',
            [
               'postcode' => 'deliveryboyOrderAddressTable.postcode',
            ]
        );
     }

    /**
     * Left Join transaction table.
     *
     * @param Select $select
     * @return void
     */
    public function leftJoinOrderTransactionTable($select)
    {
        $deliveryboyOrderTransaction = $this->getTable('deliveryboy_order_transaction');
        $select->joinLeft(
            $deliveryboyOrderTransaction . ' as deliveryboyOrderTxnTable',
            'main_table.id = deliveryboyOrderTxnTable.deliveryboy_order_id',
            [
                'transaction_id' => 'IFNULL(deliveryboyOrderTxnTable.transaction_id, "Not Delivered")',
                'transaction_entity_id' => 'deliveryboyOrderTxnTable.entity_id',
            ]
        );
    }
}
