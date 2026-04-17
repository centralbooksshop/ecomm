<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ubertheme\UbThemeHelper\Block\Cart;

class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    public function setLimit($limit = 4)
    {
        $this->_maxItemCount = $limit;
    }

    public function getLimit()
    {
        return $this->_maxItemCount;
    }
}
