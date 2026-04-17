<?php

/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */

namespace Ubertheme\UbMegaMenu\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Data Helper
 *
 */
class Data extends AbstractHelper
{

    const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';

    const XML_PATH_SECURE_BASE_URL = 'web/secure/base_url';

    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    const XML_PATH_USE_REWRITES = 'web/seo/use_rewrites';

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Application config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfig;

    /**
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     *
     * @var \Ubertheme\UbMegaMenu\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     *
     * @var \Ubertheme\UbMegaMenu\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Ubertheme\UbMegaMenu\Model\GroupFactory $groupFactory
     * @param \Ubertheme\UbMegaMenu\Model\ItemFactory $itemFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Ubertheme\UbMegaMenu\Model\GroupFactory $groupFactory,
        \Ubertheme\UbMegaMenu\Model\ItemFactory $itemFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_context = $context;
        $this->_storeManager = $storeManager;
        $this->_appConfig = $config;
        $this->_categoryFactory = $categoryFactory;
        $this->_pageFactory = $pageFactory;
        $this->_blockFactory = $blockFactory;
        $this->_groupFactory = $groupFactory;
        $this->_itemFactory = $itemFactory;
        $this->_messageManager = $messageManager;

        parent::__construct($context);
    }

    /**
     * @param null $key
     * @param array $data
     * @return mixed|null
     */
    public function getConfigValue($key = null, $data = [])
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Store\Model\StoreManagerInterface $manager */
        $manager = $om->get('\Magento\Framework\App\ScopeResolverInterface');
        $scopeCode = $manager->getScope()->getCode();

        $currentStoreCode = $this->_storeManager->getStore()->getCode();
        $currentWebsiteCode = $this->_storeManager->getWebsite()->getCode();

        if ($scopeCode == $currentStoreCode) {
            $scope = ScopeInterface::SCOPE_STORES;
        } elseif ($scopeCode == $currentWebsiteCode) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
        } else {
            $scope = 'default';
            //$scopeId = 0;
            $scopeCode = '';
        }

        $sections = ['ubmegamenu'];
        $value = null;
        if (isset($data[$key])) {
            $value = $data[$key];
        } else {
            foreach ($sections as $section) {
                $groups = $this->_appConfig->getValue($section, $scope, $scopeCode);
                if ($groups) {
                    foreach ($groups as $configs) {
                        if (isset($configs[$key])) {
                            $value = $configs[$key];
                            break;
                        }
                    }
                }
                if ($value)
                    break;
            }
        }

        return $value;
    }

    /**
     * @param null $storeId
     * @param bool $isFilter
     * @param bool $countProduct
     * @return array
     */
    public function getCategoryOptions($storeId = null, $isFilter = false, $countProduct = false)
    {
        $store = $this->getStore($storeId);
        $parent_id = $store->getRootCategoryId();
        if ($store->getId() == Store::DEFAULT_STORE_ID) {
            $defaultStoreItems = $this->_categoryFactory->create()->getCollection()
                ->addFieldToFilter('parent_id', ['in' => [$parent_id]]);
            $parent_id = $defaultStoreItems->getFirstItem()->getId();
        }

        //get categories
        $categories = $this->getCategories($store->getId(), $parent_id);

        //build tree options
        $options = $this->buildTree(
            $categories, $parent_id,
            99,
            'name',
            'entity_id',
            'parent_id',
            $isFilter,
            $countProduct
        );

        return $options;
    }

    /**
     * @param array $storeIds
     * @param bool $isFilter
     * @return mixed
     */
    public function getCMSPageOptions($storeIds = [], $isFilter = false)
    {
        if (!$storeIds) {
            $storeIds[] = $this->getStore()->getId();
        }

        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $collection = $this->_pageFactory->create()->getCollection()
            ->addFieldToSelect(['page_id', 'identifier', 'title'])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($collection->getItems() as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    /**
     * @param array $storeIds
     * @return array
     */
    public function getStaticBlockOptions($storeIds = [])
    {
        $options = [];

        if (!$storeIds) {
            $storeIds[] = $this->getStore()->getId();
        }

        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $collection = $this->_blockFactory->create()->getCollection()
            ->addFieldToSelect(['block_id', 'identifier', 'title'])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($collection->getItems() as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    /**
     * @param int $storeId
     * @param int $parentId
     * @return mixed
     */
    public function getCategories($storeId = 0, $parentId = 0)
    {
        $collection = $this->_categoryFactory->create()->getCollection()
            ->addFieldToSelect(['entity_id', 'parent_id', 'name', 'level'])
            ->setStoreId($storeId)
            ->addIsActiveFilter();

        if ($parentId) {
            $collection->addFieldToFilter('path', ['like' => '%' . $parentId . '/%']);
        }

        $collection->getSelect()->order('position ASC');

        return $collection->load();
    }

    /**
     * @param $menuId
     * @return mixed
     */
    public function getMenuGroupById($menuId)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_groupFactory->create()->getCollection()
            ->addFieldToSelect(['group_id', 'title', 'identifier', 'animation_type', 'is_active'])
            ->addFieldToFilter('group_id', ['eq' => $menuId])
            ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED])
            ->addStoreFilter($storeId, true)
            ->addOrder('group_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        return $collection->getFirstItem();
    }

    /**
     * @param $menuKey
     * @return mixed
     */
    public function getMenuGroupByKey($menuKey, $customerGroupId = 0)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_groupFactory->create()->getCollection()
            ->addFieldToSelect(['group_id', 'title', 'identifier', 'animation_type', 'is_active'])
            ->addFieldToFilter('identifier', ['eq' => $menuKey])
            ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED])
            ->addStoreFilter($storeId, true)
            ->addCustomerGroupFilter($customerGroupId)
            ->addOrder('group_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        return $collection->getFirstItem();
    }

    /**
     * @param $menuGroupId
     * @param array $configs
     * @return \Magento\Framework\DataObject[]|null
     */
    public function getMenuItems($menuGroupId, $configs = array())
    {
        $items = null;
        if ($menuGroupId) {
            $collection = $this->_itemFactory->create()->getCollection()
                ->addFieldToFilter('group_id', ['eq' => $menuGroupId])
                ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Item::STATUS_ENABLED])
                ->addFieldToFilter('level', ['lteq' => $configs['end_level']])
                ->addOrder('level', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
                ->addOrder('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
            $items = $collection->getItems();
        }

        return $items;
    }

    /**
     * Build tree items function
     *
     * @param int $rootId
     * @param $models
     * @param int $maxLevel
     * @param string $labelField
     * @param string $keyField
     * @param string $parentField
     * @param bool|false $isFilter
     * @param bool|false $countProduct
     * @return array
     */

    public function buildTree(
        $models,
		$rootId = 0,
        $maxLevel = 99,
        $labelField = "name",
        $keyField = "entity_id",
        $parentField = "parent_id",
        $isFilter = false,
        $countProduct = false
    )
    {
        //grouping
        @$children = [];
        foreach ($models as $model) {
            $pt = $model->getData($parentField);
            $list = (isset($children[$pt]) && $children[$pt]) ? $children[$pt] : [];
            array_push($list, $model);
            $children[$pt] = $list;
        }

        //build tree
        $lists = $this->_toTree(
            $rootId,
            '',
            [],
            $children,
            $parentField,
            $keyField,
            $labelField,
            0,
            $maxLevel,
            $countProduct
        );


        if ($isFilter) {
            $outputs = ['0' => __('All')];
        }

        foreach ($lists as $id => $list) {
            $lists[$id]->$labelField = $lists[$id]->$labelField;
            $outputs[$lists[$id]->getData($keyField)] = $lists[$id]->$labelField;
        }

        return $outputs;
    }

    /**
     * Generate tree items
     *
     * @param $id
     * @param $indent
     * @param $list
     * @param $children
     * @param int $maxLevel
     * @param int $level
     * @param $label
     * @param $key
     * @param $parent
     * @param bool|false $countProduct
     * @return mixed
     */
    protected function _toTree(
        $id, $indent, $list, &$children, $parent, $key, $label, $level = 0, $maxLevel = 99, $countProduct = false
    )
    {
        if (@$children[$id] && $level <= $maxLevel) {

            foreach ($children[$id] as $v) {
                $id = $v->getData($key);

                $pre = '';
                $spacer = '--- ';
                if ($v->getData($parent) == 0) {
                    $txt = $v->getData($label);
                } else {
                    $txt = $pre . $v->getData($label);
                }

                $list[$id] = $v;
                $list[$id]->$label = "{$indent}{$txt}";

                if ($countProduct) {
                    $list[$id]->$label .= " (" . $v->getProductCount() . ")";
                }

                //$list[$id]->children = count(@$children[$id]);
                $list = $this->_toTree(
                    $id,
                    $indent . $spacer,
                    $list,
                    $children,
                    $parent,
                    $key,
                    $label,
                    $level + 1,
                    $maxLevel,
                    $countProduct
                );
            }
        }

        return $list;
    }

    /**
     * @param null $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    protected function getStore($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = (int)$this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        }

        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $om->get('\Magento\Backend\App\Action\Context');

        return $context->getRequest();
    }

    /**
     * @param string $type
     * @param $param
     * @param bool $getSingle
     */
    public function deleteRelatedMenuItems($type, $param, $getSingle = false)
    {
        //check exists of menu item with CMS page and delete items
        $collection = $this->getRelatedMenuItems($type, $param, $getSingle);
        if ($collection) {
            // delete and add message delete menu items
            if ($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS) {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->_messageManager->addWarning( __('Menu items associated with this CMS Page have been deleted.') );
            } elseif($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
                $collection->delete();
                $this->_messageManager->addWarning( __('Menu items associated with this Category have been deleted.') );
            }
        }
    }

    /**
     * @param string $type
     * @param $param
     * @param bool $getSingle
     * @return \Magento\Framework\DataObject|\Magento\Framework\DataObject[]|null
     */
    public function getRelatedMenuItems($type, $param, $getSingle)
    {
        $rs = null;
        $collection = null;
        $itemFactory = $this->_itemFactory->create();
        if ($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS) {
            $collection = $itemFactory->getCollection()
                ->addFieldToSelect(['item_id'])
                ->addFieldToFilter('link_type', ['eq' => $type])
                ->addFieldToFilter('cms_page', $param)
                ->load();

        } elseif($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
            $collection = $itemFactory->getCollection()
                ->addFieldToSelect(['item_id', 'group_id'])
                ->addFieldToFilter('link_type', ['eq' => $type])
                ->addFieldToFilter('category_id', ['in' => [$param]])
                ->load();
        }

        if ($collection) {
            $rs = ($getSingle) ? $collection->getFirstItem() : $collection->getItems();
        }

        return $rs;
    }
    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        $isSecure = (int) $this->scopeConfig->getValue(self::XML_PATH_SECURE_IN_FRONTEND, ScopeInterface::SCOPE_STORE);
        $urlSecure = $this->scopeConfig->getValue(self::XML_PATH_SECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
        $urlUnsecure = $this->scopeConfig->getValue(self::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
        $isUseRewrites = $this->scopeConfig->getValue(self::XML_PATH_USE_REWRITES, ScopeInterface::SCOPE_STORE);
        $url = ($isSecure) ?  $urlSecure : $urlUnsecure;

        return ($isUseRewrites) ? $url : ($url.'index.php/');
    }
}
