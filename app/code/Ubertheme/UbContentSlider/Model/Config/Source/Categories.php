<?php
/**
 * Copyright © 2015 Magento. All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Model\Config\Source;

use Magento\Store\Model\Store;

class Categories implements \Magento\Framework\Option\ArrayInterface
{
    /**
    *
    * @var \Magento\Catalog\Model\CategoryFactory $categoryFactory
    */
    protected $_categoryFactory;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {

            $this->_options = [];

            $store = $this->getStore();

            $parent_id = $store->getRootCategoryId();
            if ($store->getId() == Store::DEFAULT_STORE_ID) {
                $defaultStoreItems = $this->_categoryFactory->create()->getCollection()
                    ->addFieldToFilter('parent_id', ['in' => [$parent_id]]);
                $parent_id = $defaultStoreItems->getFirstItem()->getId();
            }

            //get categories
            $categories = $this->getCategories($store->getId(), $parent_id);

            //build tree
            $tree = $this->buildTree($categories, $parent_id, 99, 'name', 'entity_id', 'parent_id', true, true);

            //build options array
            foreach ($tree as $id => $label) {
                $this->_options[] = [
                    'label' => $label,
                    'value' => $id,
                ];
            }
        }
        return $this->_options;
    }

    protected function getCategories($storeId = 0, $parentId = 0)
    {
        $collection = $this->_categoryFactory->create()->getCollection()
            ->addFieldToSelect(['entity_id', 'parent_id', 'name', 'level'])
            ->setStoreId($storeId)
            ->addIsActiveFilter();

        if ($parentId) {
            $collection->addFieldToFilter('path', ['like' => '%'.$parentId . '/%']);
        }

        $collection->getSelect()->order('position ASC');

        return $collection->load();
    }

    protected function buildTree($models, $rootId = 0, $maxLevel = 99, $labelField = "name", $keyField = "entity_id", $parentField = "parent_id", $isFilter = false, $countProduct = false)
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
        $lists = $this->_toTree($rootId, '', [], $children, $parentField, $keyField, $labelField, 0, $maxLevel, $countProduct);


        if ($isFilter) {
            $outputs = ['0' => __("All")];
        }

        foreach ($lists as $id => $list) {
            //$lists[$id]->$labelField = "--" . $lists[$id]->$labelField;
            $lists[$id]->$labelField = $lists[$id]->$labelField;
            $outputs[$lists[$id]->getData($keyField)] = $lists[$id]->$labelField;
        }
        return $outputs;
    }

    protected function _toTree($id, $indent, $list, &$children, $parent, $key, $label, $level = 0, $maxLevel = 99, $countProduct = false)
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

                if($countProduct) {
                    $list[$id]->$label .= " (".$v->getProductCount().")";
                }

                //$list[$id]->children = count(@$children[$id]);
                $list = $this->_toTree($id, $indent . $spacer, $list, $children, $parent, $key, $label, $level + 1, $maxLevel, $countProduct);
            }
        }
        return $list;
    }

    protected function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        return $this->_storeManager->getStore($storeId);
    }

    protected function getRequest()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $om->get('\Magento\Backend\App\Action\Context');
        return $context->getRequest();
    }
}
