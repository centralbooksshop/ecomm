<?php

namespace Ubertheme\UbThemeHelper\Block\Widget;

class Categories extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'widget/categories.phtml';

    const DEFAULT_IMAGE_WIDTH = 250;
    const DEFAULT_IMAGE_HEIGHT = 250;

    /**
     * \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    public function getCategories()
    {
        $categoryIds = $this->getData('category_ids');
        if ($categoryIds) {
            $categories = $this->getCategoriesByIds($categoryIds);
        } else {
            //get by current root category id
            $currentRootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
            $recursionLevel = 1; $sorted = 1; $asCollection = 1; $toLoad = 1;
            $cacheKey = sprintf('%d-%d-%d-%d', $currentRootCategoryId, $sorted, $asCollection, $toLoad);
            if (isset($this->_storeCategories[$cacheKey])) {
                return $this->_storeCategories[$cacheKey];
            }
            $categories = $this->_categoryFactory->create()->getCategories($currentRootCategoryId, $recursionLevel, $sorted, $asCollection, $toLoad);
            $this->_storeCategories[$cacheKey] = $categories;
        }

        return $categories;
    }

    public function getCategoriesByIds($categoryIds)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_categoryFactory->create()->setStoreId($storeId)->getCollection();
        $collection->addFieldToSelect('*');
        $collection->addAttributeToFilter('entity_id', array('in', explode(',', $categoryIds)));

        return $collection->getItems();
    }

    public function getImageWidth()
    {
        return (!empty($this->getData('image_width'))) ? (int)$this->getData('image_width') : self::DEFAULT_IMAGE_WIDTH;
    }

    public function getImageHeight()
    {
        return (!empty($this->getData('image_height'))) ? (int)$this->getData('image_height') : self::DEFAULT_IMAGE_HEIGHT;
    }

    public function canShowImage()
    {
        return ($this->getData('enable_thumbnail') == 'yes') ? true : false;
    }

    public function canShowProductCounter()
    {
        return ($this->getData('enable_product_counter') == 'yes') ? true : false;
    }

    public function getImageUrl($category)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $category = $this->_categoryFactory->create()->setStoreId($storeId)->load($category->getId());
        return $category->getImageUrl();
    }

    public function getProductCount($category)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $category = $this->_categoryFactory->create()->setStoreId($storeId)->load($category->getId());
        return $category->getProductCount();
    }

}