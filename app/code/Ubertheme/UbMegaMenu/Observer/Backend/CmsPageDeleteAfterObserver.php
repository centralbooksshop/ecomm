<?php
/**
 * Copyright © 2018 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CmsPageDeleteAfterObserver implements ObserverInterface
{
    /**
     * @var \Ubertheme\UbMegaMenu\Helper\Data
     */
    protected $_helperData;

    /**
     * CmsPageDeleteAfterObserver constructor.
     * @param \Ubertheme\UbMegaMenu\Helper\Data $helperData\
     */
    public function __construct(
        \Ubertheme\UbMegaMenu\Helper\Data $helperData
    )
    {
        $this->_helperData = $helperData;
    }

    /**
     * Update related menu items after a CMS page deleted
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //check has allowed
        $isAllowed = (bool)$this->_helperData->getConfigValue('auto_sync_cmspage_menu_item');
        if (!$isAllowed) {
            return;
        }
        $resquest = $this->_helperData->getRequest();
        $pageId = $resquest->getParam('page_id');
        if ($pageId) {
            $this->_helperData->deleteRelatedMenuItems(\Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS, $pageId, false);
        }

        return $this;
    }
}