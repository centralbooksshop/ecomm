<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returnrule;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;

class Space extends AbstractModel
{
    /**
     * Retrieve space
     *
     * @param  Product $product
     * @return $this
     */
    public function getSpace(Product $product)
    {
        // Product and categories.
        if ($product->getId()) {
            $this->setData('product', $product);
        }

        return $this;
    }
}
