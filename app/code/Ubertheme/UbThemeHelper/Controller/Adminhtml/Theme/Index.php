<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Controller\Adminhtml\Theme;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbThemeHelper::listing_themes';
    const ADMIN_SAVE_SETTING_RESOURCE = 'Ubertheme_UbThemeHelper::save_settings';

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Ubertheme_UbThemeHelper::settings');


        if (!$this->_authorization->isAllowed(self::ADMIN_SAVE_SETTING_RESOURCE)) {
            $msg = __('This is only a demo theme dashboard of UB Trex Pro. You can browse all configurations, but no changes will be applied.');
            $this->messageManager->addNoticeMessage($msg);
        }

        $this->_view->renderLayout();
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
