<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Model\ResourceModel\Sales\Report\Bestsellers;

class Collection extends \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
{
    public function setLimit($limit = 5)
    {
        $this->_ratingLimit = $limit;
    }

    public function getLimit()
    {
        return $this->_ratingLimit;
    }
}
