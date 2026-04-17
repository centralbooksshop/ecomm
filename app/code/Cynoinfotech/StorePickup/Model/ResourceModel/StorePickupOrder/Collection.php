<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName ='entity_id';
      /**
       * Event prefix
       *
       * @var string
       */
    
    protected $_eventPrefix = 'store_order_collection';
    /**
     * Event object
     *
     * @var string
     */
    
    protected $_eventObject ='store_order_collection';
    
    /**
     * Define resource model
     *
     * @return void
     */
    
    protected function _construct()
    {
        $this->_init(
            'Cynoinfotech\StorePickup\Model\StorePickupOrder',
            'Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder'
        );
    }
    
    /**
     * Get SQL for get recoed count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        return $countSelect;
    }
    
    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'entity_id', $labelField = 'store_id', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
}
