<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Model\ResourceModel\Item;

/**
 * UbContentSlider slide item collection
 *
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
    * @var string
    */
    protected $_idFieldName = 'item_id';
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ubertheme\UbContentSlider\Model\Item', 'Ubertheme\UbContentSlider\Model\ResourceModel\Item');
        $this->_map['fields']['item_id'] = 'main_table.item_id';
    }

    /**
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIds = [];
        foreach ($this as $item) {
            $id = $item->getData('item_id');

            $data['value'] = $id;
            $data['label'] = $item->getData('title');

            if (in_array($id, $existingIds)) {
                $data['value'] .= '|' . $item->getData('item_id');
            } else {
                $existingIds[] = $id;
            }

            $res[] = $data;
        }

        return $res;
    }
    
    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $slideId = $om->get('Magento\Backend\Model\Session')->getSlideId();
        if ($slideId){
            $this->addFieldToFilter('slide_id', $slideId);
        }
        
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        
        return $countSelect;
    }

    /**
     * Redeclare before load method for adding event
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $slideId = $om->get('Magento\Backend\Model\Session')->getSlideId();
        if ($slideId){
            $this->addFieldToFilter('slide_id', $slideId);
        }
        
        return parent::_beforeLoad();
    }

    /**
     * Join slide relation table if there is slide filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $slideId = $om->get('Magento\Backend\Model\Session')->getSlideId();
        if ($slideId){
            $this->addFieldToFilter('slide_id', $slideId);
        }
        
        parent::_renderFiltersBefore();
    }
}
