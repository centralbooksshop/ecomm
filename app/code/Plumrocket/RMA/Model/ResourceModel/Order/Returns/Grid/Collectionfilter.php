<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Plumrocket\RMA\Model\ResourceModel\Order\Returns\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionTrait;
use Psr\Log\LoggerInterface as Logger;

class Collectionfilter extends SearchResult
{
    
    use CollectionTrait;

    /**
     * Map with relations of tables columns and aliases for filters
     *
     * @var array
     */
    protected $columnsMap = [
        'increment_id'       => 'main_table.increment_id',
		'created_at'         => 'main_table.created_at',
		'order_increment_id' => 'o.increment_id',
		'order_date'         => 'o.updated_at',
		'entity_id'          => 'main_table.entity_id',
		'customer_name'      => 'cgf.name',
		'reply_at'           => 'rm.created_at',
		'status'             => 'main_table.status',
		'note'             => 'main_table.note',
		'store_id'           => 'o.store_id',
		'customer_address'   => 'cgf.shipping_full',
		'product_name'       => 'soi.name',
		'rma_reason'         => 'rs.title',
		'school_name'         => 'o.school_name'
    ];

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'plumrocket_rma_returns',
        $resourceModel = '\Plumrocket\RMA\Model\ResourceModel\Returns'
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        foreach ($this->columnsMap as $filter => $alias) {
            $this->addFilterToMap($filter, $alias);
        }
    }

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

		$this->addOrderData()
            ->addCustomerData()
            ->addAdminData()
			->addReturnsItemData()
            ->addProductName()
            ->addRmaItems()
            ->addSchoolCode()
            ->addLastReplyData();
		if (false === $this->isArchive()) {
            //$this->addNotArchiveFilter();
        } else {
            //$this->addArchiveFilter();
        }

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$highestTime = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('cbo/payments/highest_orders_time');

		if(!empty($highestTime)) {
		   //$this->addFieldToFilter('main_table.created_at', array('from'=>$startDate, 'to'=>$endDate));
			$date1 = (new \DateTime())->modify($highestTime);
			$this->addFieldToFilter('main_table.created_at', ['gteq' => $date1->format('Y-m-d h:i:s')]);
		}

	    //if(!empty($startDate)) {
		    //$this->addFieldToFilter('main_table.created_at', array('from'=>$startDate, 'to'=>$endDate));
		//}
    }

	 /**
     * @return bool|false|int
     */
    public function isArchive()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$request = $objectManager->get('Magento\Framework\App\RequestInterface');
		return mb_strpos((string) $request->getServerValue('HTTP_REFERER'), 'returnsarchive');
    }


	/**
     * Add filter for not archive returns
     *
     * @return $this
     */
    public function addNotArchiveFilter()
    {
        $this->addFieldToFilter('main_table.is_closed', false);
        return $this;
    }

    /**
     * Add filter for archive returns
     *
     * @return $this
     */
    public function addArchiveFilter()
    {
        $this->addFieldToFilter('main_table.is_closed', true);
        return $this;
    }
}
