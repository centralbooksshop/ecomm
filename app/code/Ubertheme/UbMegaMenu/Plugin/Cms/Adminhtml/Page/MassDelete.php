<?php
/**
 * Copyright © 2018 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Plugin\Cms\Adminhtml\Page;

class MassDelete extends \Magento\Cms\Controller\Adminhtml\Page\MassDelete
{
    /**
     * @param \Magento\Cms\Controller\Adminhtml\Page\MassDelete $subject
     * @return array
     */
    public function beforeExecute(\Magento\Cms\Controller\Adminhtml\Page\MassDelete $subject)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $helperData = $om->get('\Ubertheme\UbMegaMenu\Helper\Data');

        //check has allowed
        $isAllowed = (bool)$helperData->getConfigValue('auto_sync_cmspage_menu_item');
        if (!$isAllowed) {
            return [];
        }

        /* @var \Ubertheme\UbMegaMenu\Helper\Data $helperData */
        $collection = $subject->filter->getCollection($subject->collectionFactory->create());
        foreach ($collection as $item) {
            $helperData->deleteRelatedMenuItems(\Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS, $item->getId(), false);
        }

        return [];
    }
}