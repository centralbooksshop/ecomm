<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Model\ResourceModel;

use Ubertheme\UbContentSlider\Helper\Image as ImageHelper;
use Ubertheme\UbContentSlider\Model\Item\Image as ImageModel;

/**
 * UbContentSlider slide item mysql resource
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * image model
     *
     * @var \Ubertheme\UbContentSlider\Model\Item\Image
     */
    protected $imageModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ImageModel $imageModel
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ImageModel $imageModel,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->imageModel = $imageModel;
        $this->dateTime = $dateTime;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ubcontentslider_slide_item', 'item_id');
    }

    /**
     * Process slide item data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        //delete related image uploaded
        $image = $object->getImage();
        if ($image) {
            $imagePath = $this->imageModel->getBaseDir().$image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $condition = ['item_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getMainTable(), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process slide item data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        //validate video id
        if (in_array($object->getData('content_type'), ['youtube_video', 'vimeo_video'])) {
            if (!ImageHelper::isValidVideoId($object->getData('content_type'), $object->getData('video_id'))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please re-check, the Video ID and Content Type you selected are not matched.')
                );
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * After save a slide item function
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_afterSave($object);
    }

    /**
     * Load an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
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
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Ubertheme\UbContentSlider\Model\Item $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        return $select;
    }

    /**
     * Retrieves sliders options.
     * @return array
     */
    public function getSliderOptions()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('ubcontentslider_slide'), ['slide_id', 'title']);
        return $connection->fetchAll($select);
    }
}
