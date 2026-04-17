<?php

/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;


/**
 * Ub Content Slider Data Helper
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory
     */
    protected $_resourceFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
    )
    {
        $this->_context = $context;
        $this->_storeManager = $storeManager;
        $this->_appConfig = $config;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_resourceFactory = $resourceFactory;

        parent::__construct($context);
    }

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

        $sections = ['ubcontentslider'];
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
     * @param null $path
     * @return mixed
     */
    public function getConfig($path = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    public function getSliderItems(&$config)
    {
        $items = null;
        if ($config->content_type == 'slider') {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Ubertheme\UbContentSlider\Model\Slide $model */
            $model = $om->get('Ubertheme\UbContentSlider\Model\Slide');

            if (!$config->slider_id && $config->slider_key) {
                //get slider id by slider_key (identifier) of current store
                $storeId = $this->_storeManager->getStore()->getId();
                $config->slider_id = $model->getSlideIdByIdentifier($config->slider_key, $storeId);
            }
            if ($config->slider_id) {
                //get slide items by slider id
                $model = $model->load($config->slider_id);
                if ($model->isActive()) {
                    $items = $model->getSlideItems($config->slider_id);
                } else {
                    $config->enable = false;
                }
            }
        } else {
            $items = $this->getProducts($config);
        }

        return $items;
    }

    public function getProducts($config)
    {
        if ($config->content_type == 'bestseller_products') {
            /** @var \Ubertheme\UbContentSlider\Model\ResourceModel\Sales\Report\Bestsellers\Collection $collection */
            $collection = $this->_resourceFactory->create(
                'Ubertheme\UbContentSlider\Model\ResourceModel\Sales\Report\Bestsellers\Collection'
            );
            $collection->setLimit($config->qty);
        } else {
            /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $collection = $this->_productCollectionFactory->create();
            $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

            if ($config->content_type == 'hot_products') {
                $collection->addAttributeToFilter('is_hot', 1);
            } else if ($config->content_type == 'new_products') {
                $fromDate = new \DateTime($config->from_date);
                $fromDate->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $toDate = new \DateTime($config->to_date);
                $toDate->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                $collection->addAttributeToFilter(
                    'news_from_date', [
                    'or' => [
                        0 => ['date' => true, 'to' => $toDate],
                        1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                ], 'left'
                )->addAttributeToFilter(
                    'news_to_date', [
                    'or' => [
                        0 => ['date' => true, 'from' => $fromDate],
                        1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                ], 'left'
                )->addAttributeToFilter(
                    [
                        ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                        ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                    ]
                );
            }

            //add more filters
            $collection->addMinimalPrice()->addFinalPrice()->addTaxPercents()
                ->addAttributeToSelect($config->attributesToSelect)
                ->addUrlRewrite()
                ->addStoreFilter();

            //make sort order
            //add sort setting from config
            if ($config->content_type != 'random_products') {
                $collection->addAttributeToSort($config->sort_by, $config->sort_dir);
            }
            //add more short for some case
            if ($config->content_type == 'random_products') {
                $collection->getSelect()->order('rand()');
            } else if ($config->content_type == 'new_products') {
                $collection->addAttributeToSort('news_from_date', 'desc');
            }

            //add categories filter
            if ($config->category_ids AND is_string($config->category_ids)) {
                $collection->joinTable(
                    'catalog_category_product',
                    'product_id = entity_id',
                    ['category_id'],
                    '{{table}}.category_id IN (' . $config->category_ids . ')',
                    'right'
                );
            }

            //set page size
            $collection->setPageSize($config->qty);
        }

        if ($collection->count()) {
            $collection->setCurPage(1);
        }

        return $collection;
    }


    /**
     * Get sub string from string with limit length
     *
     * @param $text
     * @param $maxchar
     * @param string $end
     * @return string
     */
    public static function subStrWords($text = '', $maxchar = 0, $end = '...')
    {
        if ($text && strlen($text) > $maxchar) {
            $words = explode(" ", $text);
            $output = '';
            $i = 0;
            while (1) {
                $length = (strlen($output) + strlen($words[$i]));
                if ($length > $maxchar) {
                    break;
                } else {
                    $output = $output . " " . $words[$i];
                    ++$i;
                };
            };
        } else {
            $output = $text;
        }

        return ($output) ? $output . $end : $output;
    }

}
