<?php
declare(strict_types=1);

/**
 * @author tjitse (Vendic)
 * Created on 17/01/2019 16:46
 */

namespace Vendic\ExtraOrderGrid\Model\ResourceModel\SplitOrder\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;
use Vendic\ExtraOrderGrid\Model\Settings;

class Collection extends OriginalCollection
{
    /**
     * @var Settings
     */
    protected $settings;

    public function __construct(
        Settings $settings,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        string $mainTable = 'sales_order_grid',
        string $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->settings = $settings;
    }

    /**
     * Copy the normal order grid collection but filter
     */
    public function _renderFiltersBefore()
    {
        $allowedStatuses = $this->getAllowedStatuses();
		$joinTable = $this->getTable('sales_order');
		$this->addFieldToFilter('status', array('in' => array('order_split')));
		$this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = sales_order.entity_id', ['parent_split_order']);
     //return $this->getSelect()->where('status = ?', 'order_split');
	  $this->getSelect()->group('main_table.entity_id');
        parent::_renderFiltersBefore();
    }

    /**
     * @return array
     */
    protected function getAllowedStatuses(): array
    {
        return $this->settings->getAllowedStatuses();
    }
}
