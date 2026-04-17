<?php
declare(strict_types=1);

/**
 * @author tjitse (Vendic)
 * Created on 17/01/2019 16:46
 */

namespace Retailinsights\Backorders\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

class Collection extends OriginalCollection
{

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        string $mainTable = 'sales_order_grid',
        string $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Copy the normal order grid collection but filter
     */
    public function _renderFiltersBefore()
    {

         $joinTable = $this->getTable('sales_order');
        return  $this->getSelect()->joinLeft(
            ['ot'=>$joinTable],
            "main_table.entity_id = ot.entity_id"
        )->where('ot.is_backeordered_items = ?','Yes')
         ->where('ot.status != ?','order_split');

        //parent::_renderFiltersBefore();
    }
}
