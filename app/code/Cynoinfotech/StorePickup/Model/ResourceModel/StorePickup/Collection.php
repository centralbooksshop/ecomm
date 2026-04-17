<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Model\ResourceModel\StorePickup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Id Field Name
     *
     * @var string
     */
    
    protected $_IdFieldName = 'entity_id';
    
    /**
     * event prefix
     *
     * @var string
     */
    
    protected $_eventPrefix = 'stores_collection';
    
    /*
    * Event Object
    *
    * @var string
	*/
    
    protected $_eventObject = 'stores_collection';
    
    protected function _construct()
    {
        $this->_init('Cynoinfotech\StorePickup\Model\StorePickup', 'Cynoinfotech\StorePickup\Model\ResourceModel\StorePickup');
    }
}
