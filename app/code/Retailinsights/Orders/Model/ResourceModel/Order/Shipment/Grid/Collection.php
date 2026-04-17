<?php
 
namespace Retailinsights\Orders\Model\ResourceModel\Order\Shipment\Grid;
//Magento\Sales\Model\ResourceModel\Order\Shipment\Order\Grid\Collection
 
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;
 
/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    protected $helper;
 
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_shipment_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    protected function _initSelect()
        {
           
            parent::_initSelect();
        }
 
    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('sales_order');
        //$this->addFieldToFilter('status', array('nin' => array('order_split')));

        $this->getSelect()->joinLeft($joinTable, 'main_table.order_id = sales_order.entity_id', ['student_name','roll_no','school_name','school_code','parent_split_order']);


        $this->getSelect()->group('main_table.entity_id');
        parent::_renderFiltersBefore();
    }
}