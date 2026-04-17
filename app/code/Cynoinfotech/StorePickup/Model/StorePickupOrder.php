<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Model;
 
class StorePickupOrder extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder');
    }
}
