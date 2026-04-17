<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
 
namespace Cynoinfotech\StorePickup\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class StorePickup extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'StorePickup';
    protected $_cacheTag = 'storepickup';
    protected $_eventPrefix = 'storepickup';
    
    protected function _construct()
    {
        $this->_init('Cynoinfotech\StorePickup\Model\ResourceModel\StorePickup');
    }
    
    /**
     * Get Identities
     *
     * @return array
     */
    
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }
    
    /**
     * Get Entity Default Value
     *
     * @return array
     */
    
    public function getDefaultValues()
    {
        $values =[];
        return $values;
    }
}
