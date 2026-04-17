<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Retailinsights\Backorders\Block\Adminhtml\Order\View;

use Retailinsights\Backorders\Helper\Data;

class Items extends \Magento\Sales\Block\Adminhtml\Order\View\Items
{
    public function isBackOrder($item)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper= $objectManager->get('Retailinsights\Backorders\Helper\Data');
        return $helper->isBackordred($item);
    }
   
}
