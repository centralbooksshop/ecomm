<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SchoolZone\Search\Block\Catalog\Product\View\Type\Bundle;

/**
 * Bundle option renderer
 * @api
 * @since 100.0.2
 */
class CustomSelect extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Select
{
    
     /**
     * Get title price for selection product
     *
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionTitlePrice($selection, $includeContainer = true)
    {
         $spl_price= $selection->getFinalPrice();
        $price = $selection->getPrice();

        $priceTitle = '<span class="product-name">'
            . $selection->getSelectionQty() * 1
            . ' x '
            . $this->escapeHtml($selection->getName())
            . '</span>';
         
         if($price > $spl_price)
            $priceTitle .= ' &nbsp; + &nbsp; <span class="bundle-oldprice product-spl-enabled">(MRP: ₹'.number_format($price,2).'</span>) Sale:  ₹'.number_format($spl_price,2);
          else 
               $priceTitle .= ' &nbsp; + &nbsp;₹'.number_format($price,2); 
                 
        // $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+'
        //     . $this->renderPriceString($selection, $includeContainer) . ($includeContainer ? '</span>' : '');
        return $priceTitle;
    }

}
