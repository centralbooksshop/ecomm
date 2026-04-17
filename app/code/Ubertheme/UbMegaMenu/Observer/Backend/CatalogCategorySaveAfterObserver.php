<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CatalogCategorySaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Ubertheme\UbMegaMenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \Ubertheme\UbMegaMenu\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Ubertheme\UbMegaMenu\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
    }

    /**
     * Update related menu items after a category saved
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //check has allowed
        $isAllowed = (bool)$this->_helper->getConfigValue('auto_sync_category_menu_item');
        if (!$isAllowed) {
            return;
        }

        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getCategory();

        if ($category->getParentId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            return;
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $parentId = $category->getParentId();

        //check exists of menu item with parent category
        $parentMenuItem = $this->_helper->getRelatedMenuItems(\Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY, [$parentId], true);
        if ($parentMenuItem->getId()) {
            //check exists of menu item with this category
            $relatedMenuItem = $this->_helper->getRelatedMenuItems(\Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY, [$category->getId()], true);
            if (!$relatedMenuItem->getId()) {
                //add new menu item with this category
                $this->addMenuItem($om, $parentMenuItem, $category);
            }

            //add message updated menu items
            $this->_messageManager->addWarning( __('Menu items associated with this Category have been updated.') );
        }

        return $this;
    }

    /**
     * @param \Ubertheme\UbMegaMenu\Model\Item $parentMenuItem
     * @param \Magento\Catalog\Model\Category\Interceptor $category
     */
    public function addMenuItem($om, $parentMenuItem, $category)
    {
        //build menu item data
        $data = [];
        $data['show_title'] = \Ubertheme\UbMegaMenu\Model\Item::SHOW_TITLE_YES;
        $data['icon_image'] = '';
        $data['font_awesome'] = '';
        $data['target'] = '_self';
        $data['show_number_product'] = \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG;
        $data['cms_page'] = null;
        $data['is_group'] = \Ubertheme\UbMegaMenu\Model\Item::IS_GROUP_NO;
        $data['mega_cols'] = 1;
        $data['mega_width'] = 0;
        $data['mega_col_width'] = 0;
        $data['mega_col_x_width'] = null;
        $data['mega_sub_content_type'] = \Ubertheme\UbMegaMenu\Model\Item::SUB_CONTENT_TYPE_CHILD_ITEMS;
        $data['custom_content'] = null;
        $data['static_blocks'] = null;
        $data['addition_class'] = null;
        $data['description'] = null;
        $data['is_active'] = \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED;
        $data['sort_order'] = 0;
        /* @var \Ubertheme\UbMegaMenu\Model\Item $parentMenuItem */
        $data['parent_id'] = $parentMenuItem->getId();
        $data['group_id'] = $parentMenuItem->getGroupId();
        $data['link_type'] = \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY;
        $data['link'] = 'dynamically';
        $data['category_id'] = $category->getId();
        $data['title'] = $category->getName();
        $data['identifier'] = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($data['title'])), '-');
        $data['is_show_category_thumb'] = 0;

        //create and save menu item
        $menuItem = $om->create('Ubertheme\UbMegaMenu\Model\Item')->setData($data)->save();

        return $menuItem;
    }
}
