<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Model\ResourceModel;

class StorePickup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }
    
    protected function _construct()
    {
        $this->_init('ci_stores', 'entity_id');
    }
    
    /**
     * retrive stores from Db by id
     *
     * @param string $id
     * @return string|bool
     */
    
    public function getStoteById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
                    ->from($this->getMainTabel(), 'name')
                    ->where('entity_id : (int)$id');
        $binds = ['entity_id'=> (int)$id];
        return $adapter->fetchOne($select, $binds);
    }
    
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel | \Cynoinfotech\StorePickup\Model\StorePickup $object
     *
     * @return $this
     */
    
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_beforeSave($object);
    }
}
