<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */

namespace Ubertheme\UbContentSlider\Model\ResourceModel;

/**
 * UbContentSlider slide mysql resource
 */
class Slide extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->_stdTimezone = $_stdTimezone;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ubcontentslider_slide', 'slide_id');
    }

    /**
     * Process slide data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        //we will delete all slide items of this slide first
        $itemIds = $this->lookupSlideItemIds((int)$object->getId());
        if ($itemIds AND is_array($itemIds)){
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $model = $om->create('Ubertheme\UbContentSlider\Model\Item');
            foreach ($itemIds as $id) {
                $model->load($id);
                $model->delete();
            }
        }

        $condition = ['slide_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getMainTable(), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process slide data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->isValidSlideIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The slide key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericSlideIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The slide key cannot be made of only numbers.')
            );
        }

        //check exists indentifier
        if ($this->isExistsIdentifier($object)){
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The slide key was used by selected store views.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Assign slide to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();

        $table = $this->getTable('ubcontentslider_slide_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['slide_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['slide_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Ubertheme\UbContentSlider\Model\Slide $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['sls' => $this->getTable('ubcontentslider_slide_store')],
                $this->getMainTable() . '.slide_id = sls.slide_id',
                []
            )->where(
                'is_active = ?',
                1
            )->where(
                'sls.store_id IN (?)',
                $storeIds
            )->order(
                'sls.store_id DESC'
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['sl' => $this->getMainTable()]
        )->join(
            ['sls' => $this->getTable('ubcontentslider_slide_store')],
            'sl.slide_id = sls.slide_id',
            []
        )->where(
            'sl.identifier = ?',
            $identifier
        )->where(
            'sls.store_id IN (?)',
            $store
        );

        if (!is_null($isActive)) {
            $select->where('sl.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     *  Check whether slide identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericSlideIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether slide identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidSlideIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    public function isExistsIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        $storeIds = (array)$object->getStores();
        array_push($storeIds, \Magento\Store\Model\Store::DEFAULT_STORE_ID);

        $select = $this->getConnection()->select()->from(
            ['sl' => $this->getMainTable()]
        )->join(
            ['sls' => $this->getTable('ubcontentslider_slide_store')],
            'sl.slide_id = sls.slide_id',
            []
        )->where(
            'sl.identifier = ?',
            $object->getData('identifier')
        )->where(
            'sls.store_id IN (?)',
            $storeIds
        );

        //if is edit
        if ($object->getData('slide_id')) {
            $select->where('sl.slide_id != ?', $object->getData('slide_id'));
        }

        $select->reset(\Zend_Db_Select::COLUMNS)->columns('sl.slide_id')->order('sls.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Check if slide identifier exist for specific store
     * return slide id if slide exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('sl.slide_id')->order('sls.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves slide title from DB by passed identifier.
     *
     * @param string $identifier
     * @return string|false
     */
    public function getSlideTitleByIdentifier($identifier)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        if ($this->_store) {
            $stores[] = (int)$this->getStore()->getId();
        }

        $select = $this->_getLoadByIdentifierSelect($identifier, $stores);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('sl.title')->order('sls.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves slide id from DB by passed identifier.
     *
     * @param string $identifier
     * @return string|false
     */
    public function getSlideIdByIdentifier($identifier)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        $stores[] = (int)$this->getStore()->getId();
        if ($this->_store) {
            $stores[] = (int)$this->getStore()->getId();
        }

        $select = $this->_getLoadByIdentifierSelect($identifier, $stores);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('sl.slide_id')->order('sls.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves slide title from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getSlideTitleById($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'title')->where('slide_id = :slide_id');

        $binds = ['slide_id' => (int)$id];

        return $connection->fetchOne($select, $binds);
    }

    /**
     * Retrieves slide identifier from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getSlideIdentifierById($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'identifier')->where('slide_id = :slide_id');

        $binds = ['slide_id' => (int)$id];

        return $connection->fetchOne($select, $binds);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $slideId
     * @return array
     */
    public function lookupStoreIds($slideId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('ubcontentslider_slide_store'),
            'store_id'
        )->where(
            'slide_id = :slide_id'
        );

        $binds = [':slide_id' => (int)$slideId];

        return $connection->fetchCol($select, $binds);
    }

    /**
     * Get slide item ids to which specified item is assigned
     *
     * @param int $slideId
     * @return array
     */
    public function lookupSlideItemIds($slideId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('ubcontentslider_slide_item'),
            'item_id'
        )->where(
            'slide_id = ?',
            (int)$slideId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * Retrieves avaiable options sliders.
     * @return array
     */
    public function getOptions( $storeFilter = false)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['sl' => $this->getMainTable()],
            ['slide_id', 'title']
        )->join(
            ['sls' => $this->getTable('ubcontentslider_slide_store')],
            'sl.slide_id = sls.slide_id',
            []
        );

        if ($storeFilter) {
            $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
            if ($this->_store) {
                $stores[] = (int)$this->getStore()->getId();
            }

            $select->where(
                'sls.store_id IN (?)',
                $stores
            );
        }

        return $connection->fetchAll($select);
    }

    public function getSlideItems($slideId)
    {
        $connection = $this->getConnection();

        $dateTimeNow = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateTimeNow = $dateTimeNow->format('Y-m-d H:i:s');
        $startTimeCondition = implode(
            ' OR ',
            [
                "(sli.start_time IS NULL)",
                "(sli.start_time <= '".$dateTimeNow."')",
            ]
        );
        $endTimeCondition = implode(
            ' OR ',
            [
                "(sli.end_time IS NULL)",
                "(sli.end_time >= '".$dateTimeNow."')",
            ]
        );

        $select = $connection->select()->from(
            ['sli' => $this->getTable('ubcontentslider_slide_item')]
        )->where(
            $startTimeCondition
        )->where(
            $endTimeCondition
        )->where(
            'sli.slide_id = ?',
            $slideId
        )->where(
            'sli.is_active = ?',
            \Ubertheme\UbContentSlider\Model\Slide::STATUS_ENABLED
        )->order('sli.sort_order ASC');

        return $connection->fetchAll($select);
    }
}
