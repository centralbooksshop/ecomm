<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */
namespace Ubertheme\UbThemeHelper\Controller\Adminhtml\Theme;

class Active extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbThemeHelper::activate_theme';

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('website');
        $storeId = $this->getRequest()->getParam('store');
        $vendor = $this->getRequest()->getParam('vendor');
        $themeId = $this->getRequest()->getParam('themeId');

        $params = [];
        $params['vendor'] = $vendor;
        if ($websiteId) {
            $params['website'] = $websiteId;
        }
        if ($storeId) {
            $params['store'] = $storeId;
        }
        /** @var \Magento\Config\Model\Config $systemConfig */
        $systemConfig = $this->_objectManager->create('Magento\Config\Model\Config');
        $systemConfig->setData($params);

        //save to system config: core_config_data
        $systemConfig->setDataByPath(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID, $themeId);
        $systemConfig->save();

        //reindex design config grid flat
        $indexerRegistry = $this->_objectManager->get('\Magento\Framework\Indexer\IndexerRegistry');
        $indexerRegistry->get(\Magento\Theme\Model\Data\Design\Config::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();

        //change default cms home page
        /** @var \Ubertheme\UbThemeHelper\App\Config $ubThemeConfig */
        $ubThemeConfig = $this->_objectManager->create('\Ubertheme\UbThemeHelper\App\Config');
        $cmsHomePage = $ubThemeConfig->getValue('cms_home_page');
        $cmsHomePage = ($cmsHomePage) ? $cmsHomePage : 'home';
        $systemConfig->setDataByPath('web/default/cms_home_page', $cmsHomePage);
        $systemConfig->save();

        $this->messageManager->addSuccess(__('Activated theme successfully. You have to refresh Magento cache to see your theme applied.'));

        $this->_redirect('ubthemehelper/theme/index', $params);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

}
