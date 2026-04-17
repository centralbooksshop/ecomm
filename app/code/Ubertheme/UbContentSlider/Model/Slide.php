<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Model;

use Ubertheme\UbContentSlider\Api\Data\SlideInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * UbContentSlider Slide Model
 *
 * @method \Ubertheme\UbContentSlider\Model\ResourceModel\Slide _getResource()
 * @method \Ubertheme\UbContentSlider\Model\ResourceModel\Slide getResource()
 */
class Slide extends \Magento\Framework\Model\AbstractModel implements SlideInterface, IdentityInterface
{
    /**#@+
     * Slide's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * UBCONTENTSLIDER slider cache tag
     */
    const CACHE_TAG = 'ubcontentslider_slide';

    /**
     * @var string
     */
    protected $_cacheTag = 'ubcontentslider_slide';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ubcontentslider_slide';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ubertheme\UbContentSlider\Model\ResourceModel\Slide');
    }

    /**
     * Receive slide store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
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
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Prepare slide's statuses.
     * Available event ubcontentslider_slide_get_available_statuses to customize statuses.
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
        return parent::getData(self::SLIDE_ID);
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
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setId($id)
    {
        return $this->setData(self::SLIDE_ID, $id);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function getOptions()
    {
        $rs = [];
        $sliderOptions = $this->_getResource()->getOptions();
        if ($sliderOptions) {
            foreach ($sliderOptions as $option) {
                $rs[$option['slide_id']] = $option['title'];
            }
        }

        return $rs;
    }

    public function getSlideIdByIdentifier($identifier, $storeId = null)
    {
        $rsModel = $this->_getResource();
        if ($storeId) {
            $rsModel->setStore($storeId);
        }
        $slideId = $rsModel->getSlideIdByIdentifier($identifier);
        return $slideId;
    }

    public function getSlideItems($slideId = null)
    {
        $id = ($slideId) ? $slideId : $this->getId();
        return $this->_getResource()->getSlideItems($id);
    }
}
