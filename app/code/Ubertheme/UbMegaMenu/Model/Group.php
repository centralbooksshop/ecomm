<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbMegaMenu\Model;

use Ubertheme\UbMegaMenu\Api\Data\GroupInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * UB Mega Menu Group Model
 *
 * @method \Ubertheme\UbMegaMenu\Model\ResourceModel\Group _getResource()
 * @method \Ubertheme\UbMegaMenu\Model\ResourceModel\Group getResource()
 */
class Group extends \Magento\Framework\Model\AbstractModel implements GroupInterface, IdentityInterface
{
    /**#@+
     * Menu Group's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * UB Mega Menu Group cache tag
     */
    const CACHE_TAG = 'ubmegamenu_group';

    /**
     * @var string
     */
    protected $_cacheTag = 'ubmegamenu_group';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ubmegamenu_group';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ubertheme\UbMegaMenu\Model\ResourceModel\Group');
    }

    /**
     * Get customer groups ids
     *
     * @return int[]
     */
    public function getCustomerGroups()
    {
        return $this->hasData('customer_groups') ? $this->getData('customer_groups') : [];
    }
    
    /**
     * Receive menu group store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Check if menu group identifier exist for specific store
     * return menu group id if menu group exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Prepare menu group's statuses.
     * Available event ubmegamenu_group_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get animation_type
     *
     * @return string
     */
    public function getAnimationType()
    {
        return $this->getData(self::ANIMATION_TYPE);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get sort order
     *
     * @return string
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setId($id)
    {
        return $this->setData(self::GROUP_ID, $id);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set animation_type
     *
     * @param string $animationType
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setAnimationType($animationType)
    {
        return $this->setData(self::TITLE, $animationType);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }
    
    public function getOptions()
    {
        $rs = [];
        $options = $this->_getResource()->getOptions();
        if ($options){
            foreach ($options as $option){
                $rs[$option['group_id']] = $option['title'];
            }
        }
        
        return $rs;
    }
    
    public function getGroupIdByIdentifier($identifier, $storeId = null){
        $rsModel = $this->_getResource();
        if ($storeId) {
            $rsModel->setStore($storeId);
        }
        $id = $rsModel->getGroupIdByIdentifier($identifier);
        return $id;
    }
    
    public function getItems($groupId = null){
        $id = ($groupId) ? $groupId : $this->getId();
        return $this->_getResource()->getItems($id);
    }

    public function getAnimationTypeOptions()
    {
        return [
            'none' => __('None'),
            'fence' =>  __('Fence'),
            'venitian' =>  __('Venitian'),
            'fan' => __('Fan'),
            'helix' => __('Helix'),
            'pop' => __('Pop'),
            'linear' => __('Linear')
        ];
    }
}
