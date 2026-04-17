<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Block;

use Magento\Customer\Model\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Ubertheme\UbMegaMenu\Helper\Data as DataHelper;
use Ubertheme\UbMegaMenu\Helper\Mega as MegaHelper;

class Menu extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_configs = [
        'is_mega_menu' => 1,
        'is_mobile_menu' => 0,
        'show_menu_title' => 0,
        'show_number_product' => 0,
        'mega_style' => 1,
        'default_mega_col_width' => 200,
        'mega_col_margin' => 20,
        'mega_content_visible_option' => null,
        'mega_content_visible_in' => null,
        'start_level' => 0,
        'end_level' => 10,
        'menu_group_id' => null,
        'menu_key' => null,
        'animation' => null,
        'addition_class' => null,
    ];

    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * @var MegaHelper
     */
    protected $_megaHelper;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        DataHelper $dataHelper,
        MegaHelper $megaHelper,
        CacheInterface $cache,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->httpContext = $httpContext;
        $this->_dataHelper = $dataHelper;
        $this->_megaHelper = $megaHelper;
        $this->cache = $cache;
    }

    /*
     * Disable Blocks HTML output cache
     */
    protected function getCacheLifetime()
    {
        return null;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        //initial configs
        $this->initialConfig($this->getData());

        //get menu group id
        if ($this->hasData('menu_id')) {
            $menuGroupId = $this->getData('menu_id');
            $menuGroup = $this->_dataHelper->getMenuGroupById($menuGroupId);
            $menuKey = $menuGroup->getIdentifier();
        } else {
            //get menu key from config
            $menuKey = ($this->hasData('menu_key')) ? trim($this->getData('menu_key')) : null;
            $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
            //get menu group id by menu key and customer group id
            $menuGroup = $this->_dataHelper->getMenuGroupByKey($menuKey, $customerGroupId);
            $menuGroupId = $menuGroup->getId();
        }

        //update some other configs
        $this->_configs['menu_title'] = $menuGroup->getTitle();
        $this->_configs['menu_key'] = $menuKey;
        $this->_configs['menu_group_id'] = $menuGroupId;
        $this->_configs['animation'] = ($this->hasData('animation'))
            ? trim($this->getData('animation'))
            : $menuGroup->getAnimationType();

        //set config params for mega helper
        $this->_megaHelper->setParams($this->_configs);

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        //assign template
        if (!$this->getTemplate()) {
            $this->setTemplate("Ubertheme_UbMegaMenu::menu.phtml");
        }

        //get menu items and generate menu items tree html
        if ($this->_configs['menu_group_id'] AND $this->_configs['menu_key']) {
            $menuHtml = $this->_generateMenuHtml($this->_configs['menu_group_id']);
        } else {
            if ($this->_configs['menu_key']) {
                $menuHtml = '<div class="no-menu">'
                    . __('The menu with the "%1" key does not exist or has not been assigned to this store view.', $this->_configs['menu_key'])
                    . '</div>';
            } else {
                $menuHtml = '<span class="no-menu">' . __('You have not set the menu to show in this store view yet.') . '</span>';
            }
        }

        //assign data to template
        $this->assign('menuHtml', $menuHtml);
        $this->assign('config', $this->_configs);

        return $this->fetchView($this->getTemplateFile());
    }

    protected function _generateMenuHtml($menuGroupId)
    {
        $html = null;
        $cacheId = $this->getCustomCacheId();
        $html = $this->cache->load($cacheId);
        if (!$html) {
            //get menu items and build menu markup html
            $items = $this->_dataHelper->getMenuItems($menuGroupId, $this->_configs);
            if ($items) {
                //build menu items data
                $this->_megaHelper->rebuildData($items);
                //generate menu
                $html = $this->_megaHelper->genMenu();
            } else {
                $html = '<span class="no-menu">' . __('There are not menu items found.') . '</span>';
            }
            //save to cache
            $this->cache->save($this->getSerializer()->serialize($html), $cacheId, [], $this->_configs['cache_lifetime']);
        } else {
            $html = $this->getSerializer()->unserialize($html);
        }

        return $html;
    }

    protected function initialConfig($data)
    {
        foreach ($this->_configs as $key => $val) {
            $this->_configs[$key] = $this->_dataHelper->getConfigValue($key, $data);
        }

        //init cache lifetime for custom cache
        $this->_configs['cache_lifetime'] = ($this->getData('cache_lifetime'))
            ? $this->getData('cache_lifetime')
            : 86400;

        return $this;
    }

    public function getIdentities()
    {
        return [
            \Magento\Store\Model\Store::CACHE_TAG,
            \Ubertheme\UbMegaMenu\Model\Group::CACHE_TAG,
            \Ubertheme\UbMegaMenu\Model\Item::CACHE_TAG,
            $this->_configs['menu_group_id'],
            $this->_configs['menu_key'],
            $this->httpContext->getValue(Context::CONTEXT_GROUP)
        ];
    }

    public function getCustomCacheId()
    {
        $cacheKeyData = [
            'UB_MEGAMENU',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->_configs['menu_group_id'],
            $this->_configs['menu_key'],
            $this->httpContext->getValue(Context::CONTEXT_GROUP)
        ];
        $cacheKeyData = array_values($cacheKeyData);
        $cacheKeyData = implode('|', $cacheKeyData);
        $cacheId = md5($cacheKeyData);

        return $cacheId;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(SerializerInterface::class);
        }
        return $this->serializer;
    }

}
