<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Model\ResourceModel;
 
class StorePickupOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    
    protected $storeManager;
     
    protected $store = null;
    
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
    }
     
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ci_stores_order', 'entity_id');
    }
    
    public function SavePickupOrder($data = '')
    {
        // Save into ci_stores_order Table
        $table_ci_stores_order = 'ci_stores_order';
        if (is_array($data)) {
            $this->getConnection()->insert($table_ci_stores_order, $data);
        }
        return;
    }
    
    public function getStorepickupById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
                    ->from($this->getMainTabel(), 'name')
                    ->where('entity_id : (int)$id');
        $binds = ['entity_id'=> (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_beforeSave($object);
    }
}
