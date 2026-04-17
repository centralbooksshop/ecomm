<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Retailinsights\ProductIssues\Model\Rewrite\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;

class Bundle extends \Magento\Bundle\Model\Product\CopyConstructor\Bundle
{
    /**
     * Duplicating bundle options and selections
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate)
    {
        if ($product->getTypeId() != Type::TYPE_BUNDLE) {
            //do nothing if not bundle
            return;
        }

        $bundleOptions = $product->getExtensionAttributes()->getBundleProductOptions() ?: [];
        $duplicatedBundleOptions = [];
        foreach ($bundleOptions as $key => $bundleOption) {
            // $duplicatedBundleOptions[$key] = clone $bundleOption;
            $duplicatedBundleOption = clone $bundleOption;
            /**
             * Set option and selection ids to 'null' in order to create new option(selection) for duplicated product,
             * but not modifying existing one, which led to lost of option(selection) in original product.
             */
            foreach ($duplicatedBundleOption->getProductLinks() as $productLink) {
                $productLink->setSelectionId(null);
            }
            $duplicatedBundleOption->setOptionId(null);
            $duplicatedBundleOptions[$key] = $duplicatedBundleOption;
        }
        $duplicate->getExtensionAttributes()->setBundleProductOptions($duplicatedBundleOptions);
    }
}
